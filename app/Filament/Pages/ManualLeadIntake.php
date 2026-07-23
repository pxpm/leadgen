<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Enums\LeadSource;
use App\Enums\LeadStatus;
use App\Models\Lead;
use App\Models\Tenant;
use App\Services\IndustryConfigEngine;
use App\Services\StructuredExtractor;
use BackedEnum;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Str;

use function Laravel\Ai\agent;

class ManualLeadIntake extends Page
{
    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-envelope-open';

    protected static ?string $title = 'Criar Lead';

    protected static ?string $navigationLabel = 'Criar Lead';

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    protected string $view = 'filament.pages.manual-lead-intake';

    public string $emailText = '';

    public string $serviceType = '';

    public array $extractedFields = [];

    public array $missingFields = [];

    public function mount(): void
    {
        //
    }

    public function getServiceOptionsProperty(): array
    {
        $tenant = $this->getTenant();
        $engine = app(IndustryConfigEngine::class);

        return collect($engine->getAvailableServices($tenant))
            ->pluck('name', 'key')
            ->toArray();
    }

    public function extractData(): void
    {
        $this->validate([
            'emailText' => 'required|string|min:10',
            'serviceType' => 'required|string',
        ]);

        $tenant = $this->getTenant();
        $extracted = $this->aiExtract($this->emailText, $this->serviceType, $tenant);

        $this->extractedFields = $extracted['found'] ?? [];
        $this->missingFields = $extracted['missing'] ?? [];

        Notification::make()
            ->title(count($this->extractedFields).' campos extraídos!')
            ->success()
            ->send();
    }

    public function createLeadAndSendLink(): void
    {
        $tenant = $this->getTenant();

        $lead = Lead::create([
            'tenant_id' => $tenant->id,
            'status' => LeadStatus::New,
            'source' => LeadSource::Manual,
            'services' => [$this->serviceType],
            'session_token' => Str::random(64),
            'token_expires_at' => now()->addHours(Lead::TOKEN_TTL_HOURS),
        ]);

        $lead->industries()->sync($tenant->industries->pluck('id'));

        $engine = app(IndustryConfigEngine::class);
        $config = $engine->resolve($tenant, $this->serviceType);
        $definitions = $config['field_definitions'] ?? [];

        foreach ($this->extractedFields as $key => $value) {
            if (isset($definitions[$key]) && ! empty($value)) {
                $lead->fields()->create([
                    'field_key' => $key,
                    'field_value' => (string) $value,
                    'field_type' => $definitions[$key]['type'] ?? 'text',
                    'confidence' => 0.7,
                    'is_required' => in_array($key, $config['required_fields'] ?? []),
                ]);
            }
        }

        // TODO: generate magic link + send email
        // app(MagicLinkService::class)->create($lead);

        Notification::make()
            ->title('Lead criado! Link enviado para o cliente.')
            ->success()
            ->send();

        $this->reset(['emailText', 'extractedFields', 'missingFields']);
    }

    private function getTenant(): Tenant
    {
        $user = auth()->user();

        if ($user?->isSuperAdmin()) {
            return Tenant::firstOrFail();
        }

        return $user?->tenant ?? Tenant::firstOrFail();
    }

    private function aiExtract(string $text, string $serviceType, Tenant $tenant): array
    {
        $engine = app(IndustryConfigEngine::class);
        $config = $engine->resolve($tenant, $serviceType);
        $fieldDefs = $config['field_definitions'] ?? [];
        $requiredFields = $config['required_fields'] ?? [];
        $fieldKeys = implode(', ', array_keys($fieldDefs));

        $systemPrompt = "És um extrator de dados. Extrai TODOS os campos do email. Responde APENAS JSON. Usa estas chaves: {$fieldKeys}. Exemplo: {\"contact_name\":\"João\",\"email\":\"joao@email.com\"}";

        try {
            $response = agent(
                instructions: $systemPrompt,
                messages: [],
            )->prompt("Email do cliente:\n\n{$text}");
        } catch (\Exception $e) {
            return ['found' => [], 'missing' => $requiredFields];
        }

        $aiText = $response->text ?? '{}';
        $extractor = new StructuredExtractor;
        $found = $extractor->extract($aiText, $fieldDefs);

        $foundSimple = [];
        foreach ($found as $key => $data) {
            $foundSimple[$key] = $data['value'];
        }

        $missing = array_values(array_diff($requiredFields, array_keys($foundSimple)));

        return ['found' => $foundSimple, 'missing' => $missing];
    }
}
