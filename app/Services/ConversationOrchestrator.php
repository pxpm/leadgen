<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\LeadStatus;
use App\Enums\MessageRole;
use App\Models\Lead;
use App\Models\LeadService;
use App\Models\Tenant;
use Illuminate\Support\Facades\Log;

class ConversationOrchestrator
{
    public function __construct(
        private IndustryConfigEngine $config,
        private QualificationEngine $qualification,
        private FieldExtractor $fieldExtractor,
        private TranslationService $trans,
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

        // --- SUMMARY CONFIRMATION → transition to next service ---
        // User saw the summary + "Está tudo correto?" and responded.
        if ($lead->current_field_key === Lead::SUMMARY_MARKER) {
            return $this->activateNextService($lead, $userMessage, $locale);
        }

        // --- SKIP HANDLING (widget "Saltar" chip) ---
        if ($userMessage === Lead::SKIP_MESSAGE) {
            return $this->handleSkip($lead, $resolvedConfig, $locale);
        }

        // Short-answer extraction (regex + smart matching — no AI needed for structured Q&A)
        $leadServiceId = $this->getCurrentLeadServiceId($lead);
        $rejectedKeys = $this->fieldExtractor->smartExtract($lead, $userMessage, $resolvedConfig, $locale, $leadServiceId);
        $reply = $this->buildReply($lead, $resolvedConfig, $locale, $rejectedKeys);

        // When all fields are collected, include structured summary for the widget to render
        $summary = empty($this->qualification->getMissingFields($lead))
            ? $this->buildSummaryData($lead, $resolvedConfig, $locale)
            : null;

        // Store the footer as the DB message (widget renders summary HTML instead)
        $dbReply = $summary ? $summary['footer'] : $reply;
        $lead->messages()->create(['role' => MessageRole::Assistant, 'content' => $dbReply]);

        return $this->buildResponse($lead, $resolvedConfig, $summary ? $summary['footer'] : $reply, $summary);
    }

    // ─── Response Generation (Deterministic) ─────────────────────

    /**
     * Build a deterministic response. No AI text generation.
     *
     * @param  string[]  $rejectedKeys  Keys that were extracted but failed validation
     */
    private function buildReply(Lead $lead, array $config, string $locale, array $rejectedKeys = []): string
    {
        $collected = $lead->fields->pluck('field_value', 'field_key')->toArray();
        $missing = $this->qualification->getMissingFields($lead);
        $tenant = $lead->tenant;

        // All fields collected → summary (widget renders from structured data)
        if (empty($missing)) {
            return '';
        }

        $nextField = $this->qualification->getNextField($lead);

        // Track which field the bot is asking about — smartExtract reads this
        // directly instead of fragile prompt-matching.
        if ($nextField) {
            $lead->update(['current_field_key' => $nextField['key']]);
        }

        // If the next field to ask was just rejected, give validation feedback instead
        // of a generic acknowledgment that would mislead the user into thinking it was accepted.
        if ($nextField && in_array($nextField['key'], $rejectedKeys, true)) {
            return $this->buildValidationNack($nextField, $locale, $tenant);
        }

        $ack = $this->buildAcknowledgment($collected, $config, $locale, $tenant);
        $question = $nextField['prompt'] ?? $this->trans->get('orchestrator.need_more_info', $locale, $tenant)
            ?? 'Pode dar-me mais informações?';

        return $ack ? "{$ack} {$question}" : $question;
    }

    /**
     * Build a validation-failure message when the user's answer was rejected.
     * Uses specific templates for email/phone/pattern, falls back to a generic one.
     */
    private function buildValidationNack(array $nextField, string $locale, ?Tenant $tenant): string
    {
        $key = $nextField['key'];
        $question = $nextField['prompt'] ?? 'Pode dar-me mais informações?';

        // Specific validation messages per field type
        $templates = [
            'email' => 'orchestrator.invalid_email',
            'phone' => 'orchestrator.invalid_phone',
        ];

        if (isset($templates[$key])) {
            $template = $this->trans->get($templates[$key], $locale, $tenant);

            if ($template) {
                return str_replace(':question', $question, $template);
            }
        }

        // Generic fallback for pattern mismatches or unknown rejections
        $template = $this->trans->get('orchestrator.invalid_field', $locale, $tenant);

        if ($template) {
            return str_replace(':question', $question, $template);
        }

        // Ultimate fallback (shouldn't normally be reached if seeds ran)
        return "Isso não parece estar correto. {$question}";
    }

    /**
     * Build summary reply when all fields are collected, grouped by service.
     * Returns a structured array for the widget to render with proper HTML/CSS.
     *
     * @return array{services: array, contact: array, footer: string}
     */
    private function buildSummaryData(Lead $lead, array $config, string $locale): array
    {
        $options = $config['locales'][$locale]['field_options'] ?? [];
        $tenant = $lead->tenant;
        $services = [];
        $contact = [];

        foreach ($lead->leadServices()->with('fields')->get() as $service) {
            $serviceConfig = $this->config->resolve($lead->tenant, $service->service_key);
            $fields = [];

            foreach ($service->fields as $field) {
                if ($field->field_value === Lead::DECLINED || $field->field_value === '') {
                    continue;
                }
                $fields[] = $options[$field->field_key][$field->field_value] ?? $field->field_value;
            }

            if (! empty($fields)) {
                $services[] = [
                    'icon' => $serviceConfig['icon'] ?? '',
                    'name' => $serviceConfig['service_name'] ?? $service->service_key,
                    'fields' => $fields,
                ];
            }
        }

        foreach ($lead->fields()->whereNull('lead_service_id')->get() as $field) {
            if ($field->field_value === Lead::DECLINED || $field->field_value === '') {
                continue;
            }
            $contact[] = $options[$field->field_key][$field->field_value] ?? $field->field_value;
        }

        $footer = $this->trans->get('orchestrator.summary_footer', $locale, $tenant)
            ?? 'Está tudo correto? Quer acrescentar alguma nota adicional?';

        return [
            'services' => $services,
            'contact' => $contact,
            'footer' => $footer,
        ];
    }

    /**
     * Build a short acknowledgment of collected fields.
     * Pulls from locale config for natural variety.
     */
    private function buildAcknowledgment(array $collected, array $config, string $locale, ?Tenant $tenant = null): string
    {
        if (empty($collected)) {
            return '';
        }

        $count = count($collected);

        // If name was just collected, personalize
        if (isset($collected['contact_name']) && $count === 1) {
            $name = $collected['contact_name'];

            $nameVariants = $this->trans->get('orchestrator.name_acknowledgment_variants', $locale, $tenant);
            if (! is_array($nameVariants) || empty($nameVariants)) {
                return '';
            }
            $variant = $nameVariants[array_rand($nameVariants)];

            return str_replace(':name', $name, $variant);
        }

        $variants = $this->trans->get('orchestrator.acknowledgment_variants', $locale, $tenant);
        if (! is_array($variants) || empty($variants)) {
            return '';
        }

        return $variants[array_rand($variants)];
    }

    // ─── Service Management ───────────────────────────────────────

    /**
     * Handle the __skip__ signal from the widget's "Saltar" chip.
     * Optional fields → store __declined__ and move on.
     * Required fields → respond with field_required nack.
     */
    private function handleSkip(Lead $lead, array $config, string $locale): array
    {
        $nextField = $this->qualification->getNextField($lead);
        $tenant = $lead->tenant;

        if (! $nextField) {
            // No field to skip — fall through to normal reply
            $reply = $this->buildReply($lead, $config, $locale);
            $lead->messages()->create(['role' => MessageRole::Assistant, 'content' => $reply]);

            return $this->buildResponse($lead, $config, $reply);
        }

        if ($nextField['required']) {
            $question = $nextField['prompt']
                ?? $this->trans->get('orchestrator.need_more_info', $locale, $tenant)
                ?? 'Pode dar-me mais informações?';
            $nack = $this->trans->get('orchestrator.field_required', $locale, $tenant);
            $reply = $nack
                ? str_replace(':question', $question, $nack)
                : "Este campo é obrigatório. {$question}";
            $lead->messages()->create(['role' => MessageRole::Assistant, 'content' => $reply]);

            return $this->buildResponse($lead, $config, $reply);
        }

        // Optional field — store declined and move on
        $leadServiceId = $this->getCurrentLeadServiceId($lead);
        $isShared = in_array($nextField['key'], $config['shared_fields']['required'] ?? [])
                 || in_array($nextField['key'], $config['shared_fields']['optional'] ?? []);

        $lead->fields()->create([
            'lead_service_id' => $isShared ? null : $leadServiceId,
            'field_key' => $nextField['key'],
            'field_type' => $nextField['type'] ?? 'text',
            'field_value' => Lead::DECLINED,
            'confidence' => 0.0,
            'is_required' => false,
        ]);
        $lead->unsetRelation('fields');
        Log::channel('ai')->debug('AI: field skipped', ['key' => $nextField['key']]);

        $reply = $this->buildReply($lead, $config, $locale);
        $lead->messages()->create(['role' => MessageRole::Assistant, 'content' => $reply]);

        return $this->buildResponse($lead, $config, $reply);
    }

    /**
     * Build the standard response array after qualification processing.
     */
    private function buildResponse(Lead $lead, array $config, string $reply, ?array $summary = null): array
    {
        $this->qualification->maybeComplete($lead);
        $lead->refresh();

        // Only transition to the next service when there are truly no more fields
        // to ask (required + optional + conditional).
        $nextField = $this->qualification->getNextField($lead);

        // All fields collected + pending services → show summary, wait for user to confirm.
        // Don't auto-transition — the user should review and optionally add notes first.
        if ($lead->status === LeadStatus::Qualified && ! empty($lead->pending_services) && ! $nextField) {
            $lead->update(['current_field_key' => Lead::SUMMARY_MARKER]);

            // Return the summary as-is, let user respond before transitioning
            $isComplete = false;

            return [
                'reply' => $reply,
                'summary' => $summary,
                'is_complete' => $isComplete,
                'phase' => 'qualification',
                'progress' => ['collected' => $lead->fields()->count(), 'required' => count($config['required_fields'] ?? [])],
                'next_field' => null,
                'lead' => ['id' => $lead->id, 'session_token' => $lead->session_token, 'service_type' => $lead->service_type],
            ];
        }

        // The conversation is "complete" from the widget's perspective only when
        // there's nothing left to ask — not when the lead status says qualified.
        $isComplete = ! $nextField && empty($lead->pending_services);

        Log::channel('ai')->debug('AI: result', [
            'lead_id' => $lead->id,
            'is_complete' => $isComplete,
            'collected' => $lead->fields()->count(),
            'missing' => $this->qualification->getMissingFields($lead),
        ]);

        return [
            'reply' => $reply,
            'summary' => $summary,
            'is_complete' => $isComplete,
            'phase' => 'qualification',
            'progress' => ['collected' => $lead->fields()->count(), 'required' => count($config['required_fields'] ?? [])],
            'next_field' => $nextField,
            'lead' => ['id' => $lead->id, 'session_token' => $lead->session_token, 'service_type' => $lead->service_type],
        ];
    }

    // ─── Service Management (original) ─────────────────────────────

    private function activateService(Lead $lead, string $serviceKey, string $locale): array
    {
        $lead->update(['service_type' => $serviceKey]);
        $lead->refresh();
        $resolvedConfig = $this->config->resolve($lead->tenant, $serviceKey);
        $serviceName = $resolvedConfig['service_name'] ?? $serviceKey;
        $nextField = $this->qualification->getNextField($lead);
        $tenant = $lead->tenant;
        $hasMultiple = ! empty($lead->pending_services);

        // When the first question is the name, use a warm introduction template
        if ($nextField && $nextField['key'] === 'contact_name') {
            $key = $hasMultiple ? 'orchestrator.service_activation_multi_name_first' : 'orchestrator.service_activation_name_first';
            $template = $this->trans->get($key, $locale, $tenant)
                ?? 'Vou ajudar-te com o serviço de :service. Vamos primeiro apresentar-nos, como te chamas?';
            $greeting = str_replace(':service', $serviceName, $template);
        } else {
            $firstQuestion = $nextField['prompt'] ?? $this->trans->get('orchestrator.default_greeting', $locale, $tenant)
                ?? 'Como posso ajudar?';
            $template = $this->trans->get('orchestrator.service_activation', $locale, $tenant)
                ?? 'Perfeito! Vou ajudar com o serviço de :service. :question';
            $greeting = str_replace([':service', ':question'], [$serviceName, $firstQuestion], $template);
        }

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

    /**
     * Transition to the next pending service. Called after the user confirms
     * the summary or when auto-transitioning from a completed service.
     *
     * @param  string  $userResponse  The user's response to the summary (may contain notes)
     */
    private function activateNextService(Lead $lead, string $userResponse, string $locale): array
    {
        $pending = $lead->pending_services;
        $nextService = array_shift($pending);

        // Safety: shouldn't be called with no pending services, but guard anyway
        if (! $nextService) {
            Log::warning('activateNextService called with no pending services', ['lead_id' => $lead->id]);

            return $this->buildResponse($lead, $this->config->resolve($lead->tenant, $lead->service_type), $userResponse);
        }

        // Store whatever the user wrote as notes, and acknowledge it
        $ack = '';
        $trimmed = trim($userResponse);

        if (! empty($trimmed)) {
            $lead->update(['notes' => $trimmed]);
            $variants = $this->trans->get('orchestrator.acknowledgment_variants', $locale, $lead->tenant);
            $ack = is_array($variants) ? $variants[array_rand($variants)].' ' : 'Obrigado, tomei nota. ';
        }

        $lead->update([
            'status' => LeadStatus::InProgress,
            'service_type' => $nextService,
            'pending_services' => $pending,
        ]);
        $lead->refresh();

        $nextConfig = $this->config->resolve($lead->tenant, $nextService);
        $nextName = $nextConfig['service_name'] ?? $nextService;
        $transitionTemplate = $this->trans->get('orchestrator.service_transition', $locale, $lead->tenant)
            ?? "\n\nAgora vamos falar sobre {$nextName}.";
        $transition = str_replace(':service', $nextName, $transitionTemplate);

        $nextField = $this->qualification->getNextField($lead);
        $firstQuestion = $nextField['prompt'] ?? 'Como posso ajudar?';
        $reply = "{$ack}{$transition} {$firstQuestion}";

        // Track the new field so the next message doesn't re-trigger __summary__
        if ($nextField) {
            $lead->update(['current_field_key' => $nextField['key']]);
        }

        $lead->messages()->create(['role' => MessageRole::Assistant, 'content' => $reply]);

        return [
            'reply' => $reply,
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
            // Create LeadService records for all selected services
            $this->createLeadServices($lead, $validSelection);

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
            $this->createLeadServices($lead, [$matched]);

            $lead->update(['service_type' => $matched]);
            $lead->refresh();
            $resolvedConfig = $this->config->resolve($lead->tenant, $matched);
            $greeting = $resolvedConfig['locales'][$locale]['ai_prompt']['greeting_message']
                ?? $this->trans->get('orchestrator.default_greeting', $locale, $lead->tenant)
                ?? 'Como posso ajudar?';
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
        $template = $this->trans->get('orchestrator.service_selection_prompt', $locale, $lead->tenant)
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

    // ─── LeadService Helpers ──────────────────────────────────────

    /**
     * Create LeadService records for the given service keys.
     * Each service gets its own row so fields can be attributed per-service.
     */
    private function createLeadServices(Lead $lead, array $serviceKeys): void
    {
        $existingKeys = $lead->leadServices()->pluck('service_key')->toArray();
        $order = $lead->leadServices()->max('order') ?? 0;

        foreach ($serviceKeys as $key) {
            if (in_array($key, $existingKeys)) {
                continue;
            }
            $order++;
            $lead->leadServices()->create([
                'service_key' => $key,
                'status' => 'in_progress',
                'order' => $order,
            ]);
        }
    }

    /**
     * Get the current LeadService ID for the active service_type.
     */
    private function getCurrentLeadServiceId(Lead $lead): ?int
    {
        if (! $lead->service_type) {
            return null;
        }

        return $lead->leadServices()
            ->where('service_key', $lead->service_type)
            ->latest('order')
            ->value('id');
    }
}
