<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\FollowUpScenario;
use App\Models\EmailLearningExample;
use App\Models\Lead;
use Illuminate\Support\Facades\Log;

use function Laravel\Ai\agent;

class EmailComposer
{
    /**
     * Compose a follow-up email using AI.
     *
     * @param  array<string>  $selectedItems  Reasons, fields, or stage selected by the contractor
     * @param  string|null  $freeText  Contractor's custom notes
     */
    public function compose(
        Lead $lead,
        FollowUpScenario $scenario,
        array $selectedItems,
        ?string $freeText = null,
        ?string $tenantName = null,
    ): string {
        $config = config("follow_up.scenarios.{$scenario->value}");
        $locale = $lead->tenant->locale ?? 'pt';
        $promptConfig = $config['locales'][$locale] ?? $config['locales']['pt'];
        $aiConfig = config('follow_up.ai');

        $systemPrompt = $promptConfig['ai_system_prompt'];

        // Fetch similar past examples for few-shot learning (contractor's style)
        $examples = EmailLearningExample::findSimilar(
            tenantId: $lead->tenant_id,
            scenario: $scenario,
            reasons: $selectedItems,
        );

        $userPrompt = $this->buildUserPrompt(
            $lead,
            $scenario,
            $config,
            $selectedItems,
            $freeText,
            $tenantName ?? $lead->tenant->name,
            $locale,
            $examples,
        );

        Log::info('EmailComposer: requesting AI generation', [
            'lead_id' => $lead->id,
            'tenant_id' => $lead->tenant_id,
            'scenario' => $scenario->value,
            'provider' => $aiConfig['provider'],
            'model' => $aiConfig['model'],
            'system_prompt_length' => strlen($systemPrompt),
            'user_prompt_length' => strlen($userPrompt),
        ]);

        try {
            $result = agent($systemPrompt)->prompt(
                prompt: $userPrompt,
                provider: $aiConfig['provider'],
                model: $aiConfig['model'],
            );

            $text = trim($result->text);

            // Sanitize: strip any markdown formatting the AI may have added
            $text = $this->sanitizeOutput($text);

            Log::info('EmailComposer: AI generation succeeded', [
                'lead_id' => $lead->id,
                'scenario' => $scenario->value,
                'response_length' => strlen($text),
                'usage' => $result->usage?->toArray() ?? 'n/a',
            ]);

            return $text;
        } catch (\Throwable $e) {
            Log::error('EmailComposer: AI generation failed', [
                'lead_id' => $lead->id,
                'tenant_id' => $lead->tenant_id,
                'scenario' => $scenario->value,
                'provider' => $aiConfig['provider'],
                'model' => $aiConfig['model'],
                'error_class' => $e::class,
                'error_message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return $this->fallbackTemplate($lead, $scenario, $selectedItems, $freeText, $tenantName ?? $lead->tenant->name);
        }
    }

    /**
     * Build the user prompt with structured context for the AI.
     */
    private function buildUserPrompt(
        Lead $lead,
        FollowUpScenario $scenario,
        array $config,
        array $selectedItems,
        ?string $freeText,
        string $tenantName,
        string $locale,
        array $examples = [],
    ): string {
        $clientName = $lead->fields()->where('field_key', 'contact_name')->first()?->field_value ?? 'cliente';
        $serviceType = ! empty($lead->services) ? implode(' + ', $lead->services) : 'serviço';

        // Collect key lead details for context
        $details = $this->collectLeadDetails($lead, $locale);

        $reasonLabels = [];
        foreach ($selectedItems as $item) {
            // Try flat reasons first, then grouped reasons
            $label = $config['reasons'][$item] ?? null;
            if ($label === null && ! empty($config['groups'])) {
                foreach ($config['groups'] as $group) {
                    if (isset($group['reasons'][$item])) {
                        $label = $group['reasons'][$item];
                        break;
                    }
                }
            }
            $reasonLabels[] = $label ?? $item;
        }

        $prompt = "Contexto:\n";
        $prompt .= "- Empreiteiro: {$tenantName}\n";
        $prompt .= "- Cliente: {$clientName}\n";
        $prompt .= "- Serviço: {$serviceType}\n";
        if (! empty($details)) {
            $prompt .= "- Detalhes do projeto: {$details}\n";
        }

        $prompt .= "\nCenário: {$scenario->label()}\n";

        if (! empty($reasonLabels)) {
            $prompt .= 'Motivos: '.implode(', ', $reasonLabels)."\n";
        }

        if ($freeText) {
            $prompt .= "Notas do empreiteiro: {$freeText}\n";
        }

        // Inject few-shot examples from past edited emails (contractor's writing style)
        if (! empty($examples)) {
            $prompt .= "\nExemplos de como este empreiteiro escreve emails semelhantes:\n";
            foreach ($examples as $i => $example) {
                $n = $i + 1;
                $prompt .= "--- Exemplo {$n} ---\n{$example['body']}\n";
            }
            $prompt .= "Usa um estilo semelhante aos exemplos acima.\n";
        }

        $prompt .= "\nEscreve o email agora.";

        return $prompt;
    }

    /**
     * Sanitize AI output: strip markdown formatting and replace placeholders.
     */
    private function sanitizeOutput(string $text): string
    {
        // Strip bold/italic markdown: **text**, __text__, *text*, _text_
        $text = preg_replace('/\*{1,3}(.+?)\*{1,3}/', '$1', $text);
        $text = preg_replace('/_{1,3}(.+?)_{1,3}/', '$1', $text);

        return $text;
    }

    /**
     * Collect relevant lead details for AI context.
     */
    private function collectLeadDetails(Lead $lead, string $locale): string
    {
        $fields = $lead->fields()->pluck('field_value', 'field_key')->toArray();

        $relevant = [];

        // Skip contact fields (AI doesn't need them — they're in the email headers)
        $skip = ['contact_name', 'phone', 'email', 'property_address', 'postal_code', 'notes'];

        // Collect translated field values
        $config = app(IndustryConfigEngine::class)->resolve($lead->tenant, $lead->services[0] ?? null);
        $options = $config['locales'][$locale]['field_options'] ?? [];

        foreach ($fields as $key => $value) {
            if (in_array($key, $skip) || $value === Lead::DECLINED || $value === '' || $value === null) {
                continue;
            }
            $label = $options[$key][$value] ?? $value;
            $relevant[] = "{$key}: {$label}";
        }

        return implode('; ', $relevant);
    }

    /**
     * Fallback template when AI generation fails.
     */
    private function fallbackTemplate(
        Lead $lead,
        FollowUpScenario $scenario,
        array $selectedItems,
        ?string $freeText,
        string $tenantName,
    ): string {
        $clientName = $lead->fields()->where('field_key', 'contact_name')->first()?->field_value ?? 'cliente';
        $serviceType = ! empty($lead->services) ? implode(' + ', $lead->services) : 'o seu pedido';
        $config = config("follow_up.scenarios.{$scenario->value}");

        return match ($scenario) {
            FollowUpScenario::Decline => $this->fallbackDecline($clientName, $tenantName, $serviceType, $selectedItems, $config),
            FollowUpScenario::RequestInfo => $this->fallbackRequestInfo($clientName, $tenantName, $selectedItems, $config),
            FollowUpScenario::QuoteFollowUp => $this->fallbackQuoteFollowUp($clientName, $tenantName, $selectedItems),
            FollowUpScenario::General => $freeText ?? "Olá {$clientName},\n\nObrigado pelo seu contacto.\n\nCumprimentos,\n{$tenantName}",
        };
    }

    private function resolveReasonLabel(string $key, array $config): string
    {
        // Try flat reasons first
        if (isset($config['reasons'][$key])) {
            return $config['reasons'][$key];
        }
        // Try grouped reasons
        if (! empty($config['groups'])) {
            foreach ($config['groups'] as $group) {
                if (isset($group['reasons'][$key])) {
                    return $group['reasons'][$key];
                }
            }
        }

        return $key;
    }

    private function fallbackDecline(string $clientName, string $tenantName, string $serviceType, array $items, array $config): string
    {
        $reasons = array_map(fn ($i) => mb_strtolower($this->resolveReasonLabel($i, $config)), $items);
        $reasonText = match (count($reasons)) {
            0 => 'questões de agenda',
            1 => $reasons[0],
            default => implode(' e ', [implode(', ', array_slice($reasons, 0, -1)), end($reasons)]),
        };

        return "Olá {$clientName},\n\n"
            ."Agradeço muito o seu contacto e o interesse no nosso trabalho.\n\n"
            ."Depois de analisar o seu pedido para {$serviceType}, infelizmente não vamos conseguir avançar desta vez — {$reasonText}.\n\n"
            ."Lamento não poder ajudar neste momento, mas desejo-lhe a melhor sorte com o projeto. Se precisar de alguma recomendação, terei todo o gosto em ajudar.\n\n"
            ."Um abraço,\n{$tenantName}";
    }

    private function fallbackRequestInfo(string $clientName, string $tenantName, array $items, array $config): string
    {
        $infoList = implode("\n", array_map(fn ($i) => '• '.$this->resolveReasonLabel($i, $config), $items));

        return "Olá {$clientName},\n\n"
            ."Obrigado pelo seu contacto! Estou a preparar o seu orçamento e, para ser o mais preciso possível, gostava de confirmar alguns detalhes:\n\n"
            ."{$infoList}\n\n"
            ."Assim que tiver estas informações, consigo enviar-lhe o orçamento rapidamente.\n\n"
            ."Fico à espera da sua resposta. Obrigado!\n\n"
            ."Cumprimentos,\n{$tenantName}";
    }

    private function fallbackQuoteFollowUp(string $clientName, string $tenantName, array $items): string
    {
        $stage = $items[0] ?? 'first_followup';
        [$greeting, $body, $closing] = match ($stage) {
            'first_followup' => [
                'Espero que esteja bem!',
                ' passei por cá para saber se já teve oportunidade de ver o orçamento que lhe enviei. Fique à vontade para me colocar qualquer dúvida — às vezes há detalhes que fazem diferença.',
                'Se precisar de ajustes ou esclarecimentos, é só dizer.',
            ],
            'second_followup' => [
                'Tudo bem consigo?',
                ' não queria deixar de lhe perguntar se ficou alguma dúvida sobre o orçamento. Sei que estas decisões levam o seu tempo, e não há pressa nenhuma.',
                'Estou disponível para o que precisar.',
            ],
            'final_followup' => [
                'Olá, espero que esteja tudo bem.',
                ' este é só um último contacto da minha parte sobre o orçamento. Se ainda tiver interesse, terei todo o gosto em avançar. Se por acaso a obra já não fizer sentido, não se preocupe — fica para uma próxima.',
                'Muito obrigado pela atenção e um abraço.',
            ],
            default => [
                'Espero que esteja bem!',
                ' vinha só saber se já teve oportunidade de analisar o orçamento.',
                'Fico à disposição para qualquer dúvida.',
            ],
        };

        return "Olá {$clientName},\n\n"
            ."{$greeting} Só{$body}\n\n"
            ."{$closing}\n\n"
            ."Cumprimentos,\n{$tenantName}";
    }
}
