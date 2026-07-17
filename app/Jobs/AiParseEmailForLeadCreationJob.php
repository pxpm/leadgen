<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Enums\LeadSource;
use App\Enums\LeadStatus;
use App\Models\Lead;
use App\Models\LeadEmailMessage;
use App\Models\Tenant;
use App\Models\TenantEmailAccount;
use App\Services\IndustryConfigEngine;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

use function Laravel\Ai\agent;

class AiParseEmailForLeadCreationJob implements ShouldQueue
{
    use Queueable;

    private ?TenantEmailAccount $account;

    private Tenant $tenant;

    private array $messageData;

    private bool $isWebhook;

    /**
     * IMAP path — from ProcessIncomingEmailJob.
     */
    public function __construct(
        TenantEmailAccount $account,
        array $messageData,
    ) {
        $this->account = $account;
        $this->tenant = $account->tenant;
        $this->messageData = $messageData;
        $this->isWebhook = false;
    }

    /**
     * Webhook path — from ProcessIncomingEmailJob (webhook branch).
     * Uses reflection to create instance without calling the IMAP constructor,
     * since webhooks don't have a TenantEmailAccount.
     */
    public static function dispatchFromWebhook(Tenant $tenant, array $messageData): void
    {
        $reflection = new \ReflectionClass(self::class);

        /** @var self $instance */
        $instance = $reflection->newInstanceWithoutConstructor();

        $instance->account = null;
        $instance->tenant = $tenant;
        $instance->messageData = $messageData;
        $instance->isWebhook = true;

        $instance->process();
    }

    public function handle(): void
    {
        $this->process();
    }

    private function process(): void
    {
        $tenant = $this->tenant;
        $from = $this->messageData['from_address'];
        $subject = $this->messageData['subject'] ?? '(sem assunto)';
        $body = $this->cleanBody();

        Log::channel('email_webhook')->info('🧠 AiParseEmailForLeadCreationJob: starting AI extraction', [
            'tenant_id' => $tenant->id,
            'from' => $from,
            'subject' => $subject,
            'has_body' => ! empty($body),
            'body_length' => strlen($body),
        ]);

        try {
            // ── Step 1: Detect service(s) from email ──
            $engine = app(IndustryConfigEngine::class);
            $availableServices = $engine->getAvailableServices($tenant);
            $detectedServices = $this->detectServices($body, $availableServices);

            // Primary service = first detected, rest go to pending
            $primaryService = $detectedServices[0] ?? null;
            $pendingServices = count($detectedServices) > 1
                ? array_slice($detectedServices, 1)
                : [];

            Log::channel('email_webhook')->info('🔍 Service detection result', [
                'primary' => $primaryService ?? 'none',
                'pending' => $pendingServices,
                'all_detected' => $detectedServices,
                'available_services' => array_column($availableServices, 'key'),
            ]);

            // ── Step 2: Extract fields for ALL detected services ──
            $allExtractedFields = [];
            $allMessages = [];

            $servicesToExtract = $detectedServices ?: [null]; // null = generic fallback

            foreach ($servicesToExtract as $serviceKey) {
                if ($serviceKey) {
                    $config = $engine->resolve($tenant, $serviceKey);
                } else {
                    $config = [
                        'field_definitions' => [
                            'contact_name' => ['type' => 'text'],
                            'email' => ['type' => 'email'],
                            'phone' => ['type' => 'phone'],
                        ],
                        'required_fields' => ['email'],
                    ];
                }

                $extracted = $this->extractFieldsForService($body, $serviceKey, $config, $tenant);

                if ($extracted) {
                    $allExtractedFields[$serviceKey ?? 'generic'] = $extracted;
                    $allMessages[] = $extracted['message_summary'] ?? null;
                }
            }

            if (empty($allExtractedFields)) {
                Log::channel('email_webhook')->warning('❌ No fields extracted for any service');

                return;
            }

            // Merge message summaries
            $summary = implode(' | ', array_filter($allMessages));

            // ── Step 3: Create lead with all extracted fields ──
            $lead = Lead::create([
                'tenant_id' => $tenant->id,
                'industry_id' => $tenant->industry_id,
                'status' => LeadStatus::New,
                'source' => LeadSource::Email,
                'services' => $detectedServices ?: null,
                'notes' => $summary ?: null,
                'session_token' => Str::random(64),
                'token_expires_at' => now()->addHours(Lead::TOKEN_TTL_HOURS),
            ]);

            // Store ALL extracted fields from ALL services
            $totalFields = 0;
            foreach ($allExtractedFields as $serviceKey => $fields) {
                foreach ($fields as $key => $value) {
                    if ($value && is_string($value) && $key !== 'message_summary') {
                        $lead->fields()->firstOrCreate(
                            ['lead_id' => $lead->id, 'field_key' => $key],
                            [
                                'field_value' => $value,
                                'field_type' => 'text',
                                'confidence' => 0.7,
                                'is_required' => false,
                            ]
                        );
                        $totalFields++;
                    }
                }
            }

            // ── Step 4: Store the email message ──
            LeadEmailMessage::create([
                'lead_id' => $lead->id,
                'tenant_email_account_id' => $this->account?->id,
                'direction' => 'inbound',
                'message_uid' => $this->messageData['uid'] ?? null,
                'message_id_header' => $this->messageData['message_id'] ?? null,
                'in_reply_to_header' => $this->messageData['in_reply_to'] ?? null,
                'references_header' => $this->messageData['references'] ?? null,
                'subject' => $subject,
                'body_text' => $body,
                'from_address' => $from,
                'from_name' => $this->messageData['from_name'] ?? null,
                'to_addresses' => $this->messageData['to'] ?? [],
                'cc_addresses' => $this->messageData['cc'] ?? [],
                'raw_headers' => $this->messageData['headers'] ?? [],
                'ai_extracted_fields' => $allExtractedFields,
                'received_at' => $this->messageData['received_at'] ?? now(),
            ]);

            Log::info('Lead created from email', [
                'lead_id' => $lead->id,
                'from' => $from,
                'services' => array_keys($allExtractedFields),
                'total_fields' => $totalFields,
            ]);

            Log::channel('email_webhook')->info('🎉 Lead created successfully from email!', [
                'lead_id' => $lead->id,
                'tenant_id' => $tenant->id,
                'from' => $from,
                'service_type' => $primaryService ?? 'not set',
                'pending_services' => $pendingServices,
                'services_extracted' => array_keys($allExtractedFields),
                'total_fields' => $totalFields,
            ]);

            // ── Step 6: Download attachments in the background ──
            $attachments = $this->messageData['attachments'] ?? [];
            $resendEmailId = $this->messageData['resend_email_id'] ?? null;

            if (! empty($attachments) && $resendEmailId) {
                DownloadEmailAttachmentsJob::dispatch($lead, $attachments, $resendEmailId);
            }
        } catch (\Throwable $e) {
            Log::error('AI email parsing failed', [
                'account_id' => $this->account?->id,
                'error' => $e->getMessage(),
            ]);

            Log::channel('email_webhook')->error('💥 AI email parsing crashed', [
                'tenant_id' => $this->tenant->id,
                'from' => $this->messageData['from_address'],
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }

    /**
     * Clean and prepare the email body for AI processing.
     */
    private function cleanBody(): string
    {
        $body = $this->messageData['body_text'] ?? '';
        $body = strip_tags((string) $body);
        $body = html_entity_decode($body, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $body = preg_replace('/\n{3,}/', "\n\n", $body);

        return trim(Str::limit($body, 3000));
    }

    /**
     * Extract fields from the email for a specific service.
     * Returns the AI-extracted JSON or null on failure.
     *
     * @param  array<string, mixed>  $config
     * @return array<string, string>|null
     */
    private function extractFieldsForService(string $body, ?string $serviceKey, array $config, Tenant $tenant): ?array
    {
        $fieldDefs = $config['field_definitions'] ?? [];
        $requiredFields = $config['required_fields'] ?? [];

        // Remove non-extractable metadata fields
        $extractableDefs = array_filter($fieldDefs, fn (string $key) => $key !== 'notes', ARRAY_FILTER_USE_KEY);

        if (empty($extractableDefs)) {
            return null;
        }

        $locale = $tenant->locale ?? 'pt';
        $fieldKeys = implode(', ', array_keys($extractableDefs));

        Log::channel('email_webhook')->info('📋 Extracting fields for service', [
            'service' => $serviceKey ?? 'generic',
            'field_keys' => array_keys($extractableDefs),
            'required_fields' => $requiredFields,
            'total_fields' => count($extractableDefs),
        ]);

        $systemPrompt = "És um extrator de dados. Extrai TODOS os campos do email para o serviço \"{$serviceKey}\". Responde APENAS com JSON válido. Usa estas chaves: {$fieldKeys}. NUNCA inventes dados — se não encontrares um campo, omite-o do JSON. Para campos com opções, usa EXATAMENTE um dos valores fornecidos.";

        $userPrompt = $this->buildExtractionPrompt($extractableDefs, $body, $config, $locale);

        Log::channel('email_webhook')->debug("🤖 Extraction prompt for [{$serviceKey}]", [
            'user_prompt_preview' => Str::limit($userPrompt, 500),
        ]);

        try {
            $result = agent($systemPrompt)->prompt(
                prompt: $userPrompt,
                provider: config('ai.defaults.provider', 'deepseek'),
                model: config('ai.defaults.small_model', 'deepseek-chat'),
            );

            Log::channel('email_webhook')->debug("🤖 Raw AI response for [{$serviceKey}]", [
                'raw_text' => Str::limit($result->text, 1000),
            ]);

            $json = $this->extractJson($result->text);

            if (! $json) {
                Log::channel('email_webhook')->warning("❌ No valid JSON for [{$serviceKey}]", [
                    'raw_response_preview' => Str::limit($result->text, 300),
                ]);

                return null;
            }

            Log::channel('email_webhook')->info("✅ Extracted for [{$serviceKey}]", [
                'extracted_fields' => $json,
                'field_count' => count($json),
            ]);

            return $json;
        } catch (\Throwable $e) {
            Log::channel('email_webhook')->warning("Extraction failed for [{$serviceKey}]", [
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Step 1: Detect which service(s) this email is about.
     * Returns an array of service keys — first is primary, rest are additional.
     *
     * @param  array<int, array{key:string, name:string}>  $availableServices
     * @return array<int, string>
     */
    private function detectServices(string $body, array $availableServices): array
    {
        if (empty($availableServices)) {
            return [];
        }

        $options = implode(', ', array_map(
            fn (array $s): string => "{$s['key']} ({$s['name']})",
            $availableServices
        ));

        $prompt = "És um classificador de serviços. Determina TODOS os serviços que correspondem ao email. Responde APENAS com um JSON array com as chaves dos serviços. Exemplo: [\"painting\", \"roofing\"]. Opções disponíveis: {$options}. Se não tiveres certeza, responde [].";

        try {
            Log::channel('email_webhook')->debug('🤖 Service detection prompt', [
                'prompt' => $prompt,
                'body_preview' => Str::limit($body, 300),
            ]);

            $response = agent(
                instructions: $prompt,
                messages: [],
            )->prompt("Email do cliente:\n\n{$body}");

            $raw = trim($response->text ?? '[]');

            Log::channel('email_webhook')->debug('🤖 Service detection response', [
                'raw_response' => $raw,
            ]);

            // Parse JSON array from response
            $detected = json_decode($raw, true);

            if (! is_array($detected)) {
                // Try to extract array from the text (AI might add extra text)
                if (preg_match('/\[.*\]/s', $raw, $matches)) {
                    $detected = json_decode($matches[0], true);
                }
            }

            if (! is_array($detected)) {
                return [];
            }

            // Filter to only valid service keys
            $validKeys = array_column($availableServices, 'key');
            $detected = array_values(array_intersect($detected, $validKeys));

            return $detected;
        } catch (\Throwable $e) {
            Log::channel('email_webhook')->warning('Service detection failed', [
                'error' => $e->getMessage(),
            ]);

            return [];
        }
    }

    /**
     * Build the extraction prompt using service-specific field definitions.
     * Includes human-readable options for select fields so the AI picks valid values.
     *
     * @param  array<string, array{type: string, options?: array<int, string>}>  $fieldDefs
     * @param  array<string, mixed>  $config
     */
    private function buildExtractionPrompt(array $fieldDefs, string $body, array $config, string $locale): string
    {
        $subject = $this->messageData['subject'] ?? '(sem assunto)';
        $from = $this->messageData['from_address'];
        $fromName = $this->messageData['from_name'] ?? '';

        // Build field descriptions with options for select fields
        $fieldDescriptions = [];
        foreach ($fieldDefs as $key => $def) {
            $type = $def['type'] ?? 'text';

            if ($type === 'select' && ! empty($def['options'])) {
                // Include human-readable options for select fields
                $optionLabels = $this->getFieldOptionLabels($key, $def['options'], $config, $locale);
                $optionsStr = implode(' | ', array_map(
                    fn (string $val, string $label) => "{$val}={$label}",
                    array_keys($optionLabels),
                    $optionLabels
                ));
                $fieldDescriptions[] = "  \"{$key}\": \"({$type}) — opções válidas: {$optionsStr}\"";
            } else {
                $fieldDescriptions[] = "  \"{$key}\": \"({$type})\"";
            }
        }
        $fieldsJson = "{\n".implode(",\n", $fieldDescriptions)."\n}";

        return <<<PROMPT
Email recebido:

De: {$fromName} <{$from}>
Assunto: {$subject}

---
{$body}
---

Extrai as seguintes informações em JSON. Usa EXATAMENTE estas chaves.
Para campos com opções, escolhe APENAS um dos valores indicados:
{$fieldsJson}

Responde APENAS com o JSON, sem texto adicional.
PROMPT;
    }

    /**
     * Get human-readable labels for select field options in the tenant's locale.
     *
     * @param  array<int, string>  $optionKeys
     * @param  array<string, mixed>  $config
     * @return array<string, string>
     */
    private function getFieldOptionLabels(string $fieldKey, array $optionKeys, array $config, string $locale): array
    {
        $labels = $config['locales'][$locale]['field_options'][$fieldKey] ?? [];

        if (empty($labels)) {
            // Fallback: use the option keys as labels
            return array_combine($optionKeys, $optionKeys);
        }

        // Only return labels for options that exist in the field definition
        $result = [];
        foreach ($optionKeys as $key) {
            $result[$key] = $labels[$key] ?? $key;
        }

        return $result;
    }

    /**
     * Extract JSON object from AI response, handling markdown code blocks.
     *
     * @return array<string, string>|null
     */
    private function extractJson(string $text): ?array
    {
        // Try to find JSON in code blocks first
        if (preg_match('/```(?:json)?\s*(\{[^}]+\})\s*```/s', $text, $matches)) {
            $text = $matches[1];
        }

        // Find the first JSON object
        if (preg_match('/\{[^}]+\}/s', $text, $matches)) {
            $decoded = json_decode($matches[0], true);
            if (is_array($decoded)) {
                return $decoded;
            }
        }

        return null;
    }
}
