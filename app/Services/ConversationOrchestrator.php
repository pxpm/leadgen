<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\LeadStatus;
use App\Enums\MessageRole;
use App\Models\Lead;
use Illuminate\Support\Facades\Log;

use function Laravel\Ai\agent;

class ConversationOrchestrator
{
    public function __construct(
        private IndustryConfigEngine $config,
        private QualificationEngine $qualification,
        private StructuredExtractor $extractor,
        private FieldExtractor $fieldExtractor,
    ) {}

    /**
     * @param  string|array<string>|null  $serviceKeys
     */
    public function process(Lead $lead, string $userMessage, string|array|null $serviceKeys = null): array
    {
        $locale = $this->config->getLocale($lead->tenant);
        $keys = is_array($serviceKeys) ? $serviceKeys : ($serviceKeys ? [$serviceKeys] : []);

        Log::channel('ai')->debug('AI: processing', [
            'lead_id' => $lead->id,
            'service_type' => $lead->service_type,
            'service_keys' => $keys,
            'user_msg' => $userMessage,
            'collected' => $lead->fields->pluck('field_value', 'field_key')->toArray(),
        ]);

        // --- SERVICE SELECTION ---
        if (! $lead->service_type) {
            return $this->handleServiceSelection($lead, $userMessage, $locale, $keys);
        }

        // --- QUALIFICATION ---
        $resolvedConfig = $this->config->resolve($lead->tenant, $lead->service_type);

        // Step 1: AI extraction (structured JSON only, no conversation)
        $this->runAiExtraction($lead, $userMessage, $resolvedConfig);

        // Step 2: Smart-match short answers
        $this->fieldExtractor->smartExtract($lead, $userMessage, $resolvedConfig, $locale);

        // Step 3: Deterministic response (no AI text generation)
        $reply = $this->buildReply($lead, $resolvedConfig, $locale);
        $lead->messages()->create(['role' => MessageRole::Assistant, 'content' => $reply]);

        // Completion + pending services
        $this->qualification->maybeComplete($lead);
        $lead->refresh();

        if ($lead->status === LeadStatus::Qualified && ! empty($lead->pending_services)) {
            return $this->activateNextService($lead, $reply, $locale);
        }

        $isComplete = $lead->status === LeadStatus::Qualified;
        $nextField = $isComplete ? null : $this->qualification->getNextField($lead);

        Log::channel('ai')->debug('AI: result', [
            'lead_id' => $lead->id,
            'is_complete' => $isComplete,
            'collected' => $lead->fields()->count(),
            'missing' => $this->qualification->getMissingFields($lead),
        ]);

        return [
            'reply' => $reply,
            'is_complete' => $isComplete,
            'phase' => 'qualification',
            'progress' => ['collected' => $lead->fields()->count(), 'required' => count($resolvedConfig['required_fields'] ?? [])],
            'next_field' => $nextField,
            'lead' => ['id' => $lead->id, 'session_token' => $lead->session_token, 'service_type' => $lead->service_type],
        ];
    }

    // ─── AI Extraction ───────────────────────────────────────────

    /**
     * Use AI ONLY to extract structured JSON from the user message.
     * No conversation generation — just pure extraction.
     */
    private function runAiExtraction(Lead $lead, string $userMessage, array $config): void
    {
        $fieldDefinitions = $this->config->getFieldDefinitions($lead->tenant, $lead->service_type);

        // Step A: regex-based extraction (no AI, fast)
        $this->fieldExtractor->applyExtracted(
            $lead,
            $this->extractor->extract($userMessage, $fieldDefinitions),
            $config
        );

        // Step B: AI extraction for natural language / complex messages
        $locale = $this->config->getLocale($lead->tenant);
        $missing = $this->qualification->getMissingFields($lead);
        $collected = $lead->fields->pluck('field_value', 'field_key')->toArray();

        if (empty($missing)) {
            return;
        }

        $instructions = $this->buildExtractionPrompt($config, $missing, $collected, $locale);

        try {
            $response = agent()
                ->instructions($instructions)
                ->prompt($userMessage);

            $text = $response->text ?? '';
            $extracted = $this->parseExtractionResponse($text);

            if (! empty($extracted)) {
                $this->fieldExtractor->applyExtracted($lead, $extracted, $config);
                Log::channel('ai')->debug('AI: extracted', ['keys' => array_keys($extracted)]);
            }
        } catch (\Throwable $e) {
            Log::channel('ai')->error('AI extraction failed', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Build a minimal extraction-only prompt.
     */
    private function buildExtractionPrompt(array $config, array $missing, array $collected, string $locale): string
    {
        $prompts = $config['locales'][$locale]['field_prompts'] ?? [];
        $defs = $config['field_definitions'] ?? [];
        $opts = $config['locales'][$locale]['field_options'] ?? [];

        $lines = ["Extrai APENAS JSON desta mensagem. NADA de texto livre.\n"];
        $lines[] = 'Campos que ainda preciso (usa EXATAMENTE estas chaves):';

        foreach ($missing as $key) {
            $prompt = $prompts[$key] ?? $key;
            $line = "- {$key}: \"{$prompt}\"";
            $def = $defs[$key] ?? null;
            if ($def && $def['type'] === 'select' && ! empty($def['options'])) {
                $optLabels = array_map(fn ($o) => ($opts[$key][$o] ?? $o), $def['options']);
                $line .= ' [valores: '.implode(', ', $optLabels).']';
            }
            $lines[] = $line;
        }

        if (! empty($collected)) {
            $lines[] = "\nJá recolhido (NÃO extraias outra vez): ".json_encode($collected, JSON_UNESCAPED_UNICODE);
        }

        $lines[] = "\nResponde APENAS com JSON. Exemplo: {\"contact_name\":\"Pedro\",\"email\":\"pedro@email.com\"}";
        $lines[] = 'NUNCA uses markdown fences. Só JSON puro.';

        return implode("\n", $lines);
    }

    /**
     * Parse the AI's extraction response into field data.
     */
    private function parseExtractionResponse(string $response): array
    {
        // Try direct JSON decode
        $decoded = json_decode($response, true);
        if (is_array($decoded)) {
            $result = [];
            foreach ($decoded as $key => $value) {
                if (is_string($value) && $value !== '') {
                    $result[$key] = ['value' => $value, 'confidence' => 0.9, 'type' => 'text'];
                }
            }

            return $result;
        }

        // Fallback: strip markdown and try again
        $cleaned = preg_replace('/```(?:json)?\s*/', '', $response);
        $decoded = json_decode($cleaned, true);
        if (is_array($decoded)) {
            $result = [];
            foreach ($decoded as $key => $value) {
                if (is_string($value) && $value !== '') {
                    $result[$key] = ['value' => $value, 'confidence' => 0.85, 'type' => 'text'];
                }
            }

            return $result;
        }

        return [];
    }

    // ─── Response Generation (Deterministic) ─────────────────────

    /**
     * Build a deterministic response. No AI text generation.
     */
    private function buildReply(Lead $lead, array $config, string $locale): string
    {
        $collected = $lead->fields->pluck('field_value', 'field_key')->toArray();
        $missing = $this->qualification->getMissingFields($lead);

        // All fields collected → summary
        if (empty($missing)) {
            return $this->buildSummaryReply($lead, $collected, $config, $locale);
        }

        $nextField = $this->qualification->getNextField($lead);
        $ack = $this->buildAcknowledgment($collected, $config, $locale);

        // Specific handling for postal code after address
        if ($nextField && $nextField['key'] === 'postal_code' && isset($collected['property_address'])) {
            $question = $config['locales'][$locale]['orchestration']['postal_code_question']
                ?? 'E qual é o código postal dessa morada?';
        } else {
            $question = $nextField['prompt'] ?? 'Pode dar-me mais informações?';
        }

        return $ack ? "{$ack} {$question}" : $question;
    }

    /**
     * Build summary reply when all fields are collected.
     * Skips declined fields and maps raw keys to localized labels.
     */
    private function buildSummaryReply(Lead $lead, array $collected, array $config, string $locale): string
    {
        $options = $config['locales'][$locale]['field_options'] ?? [];
        $serviceName = $config['service_name'] ?? 'serviço';

        $header = str_replace(
            ':service',
            $serviceName,
            $config['locales'][$locale]['orchestration']['summary_header']
                ?? "Perfeito! Já tenho todos os dados para o seu orçamento de {$serviceName}."
        );
        $footer = $config['locales'][$locale]['orchestration']['summary_footer']
            ?? 'Está tudo correto? Quer acrescentar alguma nota adicional?';

        $lines = ["{$header}\nResumo:"];
        foreach ($collected as $key => $value) {
            if ($value === '__declined__' || $value === '') {
                continue;
            }

            $label = $options[$key][$value] ?? $value;
            $lines[] = "  • {$label}";
        }
        $lines[] = "\n{$footer}";

        return implode("\n", $lines);
    }

    /**
     * Build a short acknowledgment of collected fields.
     * Pulls from locale config for natural variety.
     */
    private function buildAcknowledgment(array $collected, array $config, string $locale): string
    {
        if (empty($collected)) {
            return '';
        }

        $count = count($collected);

        // If name was just collected, personalize
        if (isset($collected['contact_name']) && $count === 1) {
            $name = $collected['contact_name'];

            $nameVariants = $config['locales'][$locale]['name_acknowledgment_variants'] ?? [
                "Obrigado, {$name}!",
                "Perfeito, {$name}!",
                "Certo, {$name}.",
                "Ok, {$name}!",
                "Entendido, {$name}.",
            ];
            $variant = $nameVariants[array_rand($nameVariants)];

            return str_replace(':name', $name, $variant);
        }

        $variants = $config['locales'][$locale]['acknowledgment_variants'] ?? [
            'Obrigado! Já registei.',
            'Entendido!',
            'Certo, tomei nota.',
            'Ok, registado.',
            'Perfeito, continue.',
            'Já anotei essa.',
            'Obrigado pela informação.',
            'Tudo bem, siga.',
        ];

        return $variants[array_rand($variants)];
    }

    // ─── Service Management ───────────────────────────────────────

    private function activateService(Lead $lead, string $serviceKey, string $locale): array
    {
        $lead->update(['service_type' => $serviceKey]);
        $lead->refresh();
        $resolvedConfig = $this->config->resolve($lead->tenant, $serviceKey);
        $serviceName = $resolvedConfig['service_name'] ?? $serviceKey;
        $nextField = $this->qualification->getNextField($lead);

        $firstQuestion = $nextField['prompt'] ?? 'Como posso ajudar?';
        $template = $resolvedConfig['locales'][$locale]['orchestration']['service_activation']
            ?? 'Perfeito! Vou ajudar com o serviço de :service. :question';
        $greeting = str_replace([':service', ':question'], [$serviceName, $firstQuestion], $template);

        $lead->messages()->create(['role' => MessageRole::Assistant, 'content' => $greeting]);

        return [
            'reply' => $greeting,
            'is_complete' => false,
            'phase' => 'qualification',
            'progress' => ['collected' => 0, 'required' => count($resolvedConfig['required_fields'] ?? [])],
            'next_field' => $nextField,
            'lead' => ['id' => $lead->id, 'session_token' => $lead->session_token, 'service_type' => $serviceKey],
        ];
    }

    private function activateNextService(Lead $lead, string $reply, string $locale): array
    {
        $nextService = array_shift($lead->pending_services);
        $lead->update([
            'status' => LeadStatus::InProgress,
            'service_type' => $nextService,
            'pending_services' => $lead->pending_services,
        ]);
        $lead->refresh();

        $nextConfig = $this->config->resolve($lead->tenant, $nextService);
        $nextName = $nextConfig['service_name'] ?? $nextService;
        $transitionTemplate = $nextConfig['locales'][$locale]['orchestration']['service_transition']
            ?? "\n\nAgora vamos falar sobre {$nextName}.";
        $transition = str_replace(':service', $nextName, $transitionTemplate);
        $lead->messages()->create(['role' => MessageRole::Assistant, 'content' => $transition]);

        return [
            'reply' => $reply.$transition,
            'is_complete' => false,
            'phase' => 'qualification',
            'progress' => ['collected' => $lead->fields()->count(), 'required' => count($nextConfig['required_fields'] ?? [])],
            'next_field' => $this->qualification->getNextField($lead),
            'lead' => ['id' => $lead->id, 'session_token' => $lead->session_token, 'service_type' => $lead->service_type],
        ];
    }

    private function handleServiceSelection(Lead $lead, string $userMessage, string $locale, array $keys): array
    {
        $available = $this->config->getAvailableServices($lead->tenant);
        $validKeys = array_column($available, 'key');
        $validSelection = array_values(array_intersect($keys, $validKeys));

        if (! empty($validSelection)) {
            if (count($validSelection) === 1) {
                return $this->activateService($lead, $validSelection[0], $locale);
            }

            $lead->update([
                'pending_services' => array_slice($validSelection, 1),
                'service_type' => $validSelection[0],
            ]);
            $lead->refresh();

            return $this->activateService($lead, $validSelection[0], $locale);
        }

        $matched = $this->classifyService($userMessage, $lead);
        if ($matched && in_array($matched, $validKeys)) {
            $lead->update(['service_type' => $matched]);
            $lead->refresh();
            $resolvedConfig = $this->config->resolve($lead->tenant, $matched);
            $greeting = $resolvedConfig['locales'][$locale]['ai_prompt']['greeting_message'] ?? 'Como posso ajudar?';
            $lead->messages()->create(['role' => MessageRole::Assistant, 'content' => $greeting]);

            return [
                'reply' => $greeting,
                'is_complete' => false,
                'phase' => 'qualification',
                'progress' => ['collected' => 0, 'required' => count($resolvedConfig['required_fields'] ?? [])],
                'next_field' => $this->qualification->getNextField($lead),
                'lead' => ['id' => $lead->id, 'session_token' => $lead->session_token, 'service_type' => $matched],
            ];
        }

        $serviceNames = implode(', ', array_column($available, 'name'));
        $baseConfig = $this->config->resolve($lead->tenant);
        $template = $baseConfig['locales'][$locale]['orchestration']['service_selection_prompt']
            ?? 'Olá! Em que podemos ajudar? Temos estes serviços: :services.';
        $reply = str_replace(':services', $serviceNames, $template);
        $lead->messages()->create(['role' => MessageRole::Assistant, 'content' => $reply]);

        return [
            'reply' => $reply,
            'is_complete' => false,
            'phase' => 'service_selection',
            'services' => $available,
            'progress' => ['collected' => 0, 'required' => 0],
            'next_field' => null,
            'lead' => ['id' => $lead->id, 'session_token' => $lead->session_token],
        ];
    }

    private function classifyService(string $message, Lead $lead): ?string
    {
        $msg = mb_strtolower($message);

        // Check for explicit service key in AI JSON response
        if (preg_match('/"service"\s*:\s*"(\w+)"/', $msg, $m)) {
            if (in_array($m[1], $this->config->allServiceKeys($lead->tenant))) {
                return $m[1];
            }
        }

        // Check dedicated classification keywords per service (locale-aware)
        $locale = $lead->tenant->locale ?: 'pt';
        foreach ($this->config->allServiceKeys($lead->tenant) as $serviceKey) {
            $serviceConfig = $this->config->loadServiceConfig($serviceKey);
            $keywords = $serviceConfig['locales'][$locale]['keywords'] ?? [];

            foreach ($keywords as $kw) {
                if (mb_stripos($msg, $kw) !== false) {
                    return $serviceKey;
                }
            }
        }

        return null;
    }
}
