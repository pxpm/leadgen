<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Enums\LeadSource;
use App\Enums\LeadStatus;
use App\Models\Lead;
use App\Models\LeadEmailMessage;
use App\Models\Tenant;
use App\Models\TenantEmailAccount;
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
     */
    public static function dispatchFromWebhook(Tenant $tenant, array $messageData): void
    {
        (new self)->handleWebhook($tenant, $messageData);
    }

    private function handleWebhook(Tenant $tenant, array $messageData): void
    {
        $this->account = null;
        $this->tenant = $tenant;
        $this->messageData = $messageData;
        $this->isWebhook = true;

        $this->process();
    }

    public function handle(): void
    {
        $this->process();
    }

    private function process(): void
    {
        $tenant = $this->tenant;
        $locale = $tenant->locale ?? 'pt';

        $systemPrompt = match ($locale) {
            'pt' => 'És um assistente de extração de dados. Extrai informações estruturadas de leads a partir de emails. NUNCA inventes dados. Responde APENAS com JSON válido.',
            default => 'You are a data extraction assistant. Extract structured lead information from emails. NEVER invent data. Respond ONLY with valid JSON.',
        };

        $userPrompt = $this->buildPrompt();

        try {
            $result = agent($systemPrompt)->prompt(
                prompt: $userPrompt,
                provider: config('ai.defaults.provider', 'deepseek'),
                model: config('ai.defaults.small_model', 'deepseek-chat'),
            );

            $json = $this->extractJson($result->text);

            if (! $json) {
                Log::warning('AI email parsing returned no valid JSON', [
                    'account_id' => $this->account?->id,
                    'raw_response' => Str::limit($result->text, 500),
                ]);

                return;
            }

            // Create lead with extracted fields
            $lead = Lead::create([
                'tenant_id' => $tenant->id,
                'industry_id' => $tenant->industry_id,
                'status' => LeadStatus::New,
                'source' => LeadSource::DirectLink,
                'session_token' => Str::random(64),
            ]);

            // Store extracted fields
            foreach ($json as $key => $value) {
                if ($value && is_string($value) && $key !== 'message_summary') {
                    $lead->fields()->create([
                        'field_key' => $key,
                        'field_value' => $value,
                    ]);
                }
            }

            // Store the email message linked to the new lead
            LeadEmailMessage::create([
                'lead_id' => $lead->id,
                'tenant_email_account_id' => $this->account?->id,
                'direction' => 'inbound',
                'message_uid' => $this->messageData['uid'] ?? null,
                'message_id_header' => $this->messageData['message_id'] ?? null,
                'in_reply_to_header' => $this->messageData['in_reply_to'] ?? null,
                'references_header' => $this->messageData['references'] ?? null,
                'subject' => $this->messageData['subject'] ?? null,
                'body_text' => $this->messageData['body_text'] ?? null,
                'from_address' => $this->messageData['from_address'],
                'from_name' => $this->messageData['from_name'] ?? null,
                'to_addresses' => $this->messageData['to'] ?? [],
                'cc_addresses' => $this->messageData['cc'] ?? [],
                'raw_headers' => $this->messageData['headers'] ?? [],
                'ai_extracted_fields' => $json,
                'received_at' => $this->messageData['received_at'] ?? now(),
            ]);

            Log::info('Lead created from email', [
                'lead_id' => $lead->id,
                'account_id' => $this->account?->id,
                'from' => $this->messageData['from_address'],
                'extracted_fields' => array_keys($json),
            ]);
        } catch (\Throwable $e) {
            Log::error('AI email parsing failed', [
                'account_id' => $this->account?->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    private function buildPrompt(): string
    {
        $subject = $this->messageData['subject'] ?? '(sem assunto)';
        $from = $this->messageData['from_address'];
        $fromName = $this->messageData['from_name'] ?? '';
        $body = $this->messageData['body_text'] ?? '';
        // Safety: strip any HTML tags that might have slipped through
        $body = strip_tags((string) $body);
        $body = Str::limit($body, 3000);

        return <<<PROMPT
Email recebido:

De: {$fromName} <{$from}>
Assunto: {$subject}

---
{$body}
---

Extrai as seguintes informações em JSON:
{
  "name": "nome completo do remetente",
  "phone": "número de telefone (se encontrado)",
  "email": "{$from}",
  "service_type": "tipo de serviço solicitado",
  "urgency": "baixa|media|alta",
  "address": "morada (se encontrada)",
  "message_summary": "resumo de 1 frase"
}

Responde APENAS com o JSON, sem texto adicional.
PROMPT;
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
