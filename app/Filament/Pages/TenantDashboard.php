<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Enums\LeadSource;
use App\Enums\LeadStatus;
use App\Filament\Widgets\RecentLeadsTable;
use App\Filament\Widgets\RecentMissedCallsTable;
use App\Filament\Widgets\TenantStatsOverview;
use App\Models\Lead;
use App\Models\ShortLink;
use App\Models\Tenant;
use App\Models\TenantEmailAccount;
use App\Services\IndustryConfigEngine;
use App\Services\StructuredExtractor;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Str;

use function Laravel\Ai\agent;

class TenantDashboard extends Page
{
    protected static ?string $title = 'Dashboard';

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-home';

    protected string $view = 'filament.pages.tenant-dashboard';

    public ?string $shortLinkUrl = null;

    // ── Create Lead form ──
    public string $leadEmailText = '';

    public string $leadDetectedService = '';

    /** @var array<string, string> */
    public array $leadExtractedFields = [];

    /** @var array<int, string> */
    public array $leadMissingFields = [];

    public bool $leadCreated = false;

    public function dismissLink(): void
    {
        $this->shortLinkUrl = null;
    }

    public function getWidgets(): array
    {
        return [
            TenantStatsOverview::class,
            RecentLeadsTable::class,
            RecentMissedCallsTable::class,
        ];
    }

    public static function shouldRegisterNavigation(): bool
    {
        return ! auth()->user()?->isSuperAdmin();
    }

    protected function getHeaderActions(): array
    {
        $tenantId = auth()->user()?->tenant_id;

        return [
            // Connect Gmail (OAuth)
            Action::make('connectGoogle')
                ->label('Conectar Gmail')
                ->icon('heroicon-o-envelope')
                ->color('primary')
                ->url(url('/api/oauth/google/redirect'))
                ->visible(fn () => $tenantId && ! TenantEmailAccount::where('tenant_id', $tenantId)
                    ->where('provider', 'google')
                    ->where('connection_type', 'google_oauth')
                    ->exists()),

            // Connect Outlook (OAuth)
            Action::make('connectMicrosoft')
                ->label('Conectar Outlook')
                ->icon('heroicon-o-envelope-open')
                ->color('info')
                ->url(url('/api/oauth/microsoft/redirect'))
                ->visible(fn () => $tenantId && ! TenantEmailAccount::where('tenant_id', $tenantId)
                    ->where('provider', 'microsoft')
                    ->where('connection_type', 'microsoft_oauth')
                    ->exists()),

            // TODO: Re-enable when Create Lead flow is ready
            // Action::make('createLead')
            //     ->label(__('admin.manual_lead_intake.navigation_label'))
            //     ->icon('heroicon-o-plus-circle')
            //     ->color('gray')
            //     ->modal()
            //     ->modalHeading(__('admin.manual_lead_intake.title'))
            //     ->modalDescription(__('admin.manual_lead_intake.modal_description'))
            //     ->modalContent(fn () => view('filament.modals.lead-by-email'))
            //     ->modalSubmitAction(false)
            //     ->modalCancelActionLabel(__('admin.common.close'))
            //     ->slideOver(),

            Action::make('generateIntakeLink')
                ->label(__('admin.dashboard.generate_intake_link'))
                ->icon('heroicon-o-link')
                ->color('primary')
                ->action(function (): void {
                    $tenant = auth()->user()->tenant;

                    $shortLink = ShortLink::create([
                        'hash' => ShortLink::generateHash(),
                        'tenant_id' => $tenant->id,
                        'source' => 'direct_link',
                        'expires_at' => now()->addHours(24),
                    ]);

                    $this->shortLinkUrl = url('/s/'.$shortLink->hash);
                }),
        ];
    }

    // ── Create Lead: Extract data from email text ──

    public function extractLeadData(): void
    {
        $this->validate([
            'leadEmailText' => 'required|string|min:10',
        ]);

        $tenant = $this->getTenant();
        $extracted = $this->aiExtract($this->leadEmailText, $tenant);

        $this->leadDetectedService = $extracted['service_type'] ?? '';
        $this->leadExtractedFields = $extracted['found'] ?? [];
        $this->leadMissingFields = $extracted['missing'] ?? [];
        $this->leadCreated = false;

        $count = count($this->leadExtractedFields);
        $service = $this->leadDetectedService ?: 'serviço desconhecido';

        Notification::make()
            ->title("{$count} campos extraídos! Serviço: {$service}")
            ->success()
            ->send();
    }

    // ── Create Lead: Create lead from extracted data ──

    public function createLeadFromEmail(): void
    {
        $tenant = $this->getTenant();

        $lead = Lead::create([
            'tenant_id' => $tenant->id,
            'industry_id' => $tenant->industry_id,
            'status' => LeadStatus::New,
            'source' => LeadSource::Manual,
            'services' => [$this->leadDetectedService],
            'session_token' => Str::random(64),
            'token_expires_at' => now()->addHours(Lead::TOKEN_TTL_HOURS),
        ]);

        $engine = app(IndustryConfigEngine::class);
        $config = $engine->resolve($tenant, $this->leadDetectedService);
        $definitions = $config['field_definitions'] ?? [];

        foreach ($this->leadExtractedFields as $key => $value) {
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

        $this->leadCreated = true;
        $this->reset(['leadEmailText', 'leadExtractedFields', 'leadMissingFields']);

        Notification::make()
            ->title('Lead criado! Link enviado para o cliente.')
            ->success()
            ->send();
    }

    // ── Create Lead: Reset form ──

    public function resetLeadForm(): void
    {
        $this->reset(['leadEmailText', 'leadDetectedService', 'leadExtractedFields', 'leadMissingFields', 'leadCreated']);
    }

    // ── Helpers ──

    private function getTenant(): Tenant
    {
        $user = auth()->user();

        if ($user?->isSuperAdmin()) {
            return Tenant::firstOrFail();
        }

        return $user?->tenant ?? Tenant::firstOrFail();
    }

    private function aiExtract(string $text, Tenant $tenant): array
    {
        $engine = app(IndustryConfigEngine::class);
        $availableServices = $engine->getAvailableServices($tenant);
        $serviceKeys = implode(', ', array_column($availableServices, 'key'));
        $serviceNames = implode(', ', array_column($availableServices, 'name'));

        // Step 1: Detect service type from email
        $detectedService = $this->detectServiceFromEmail($text, $availableServices);

        if (! $detectedService) {
            // Fallback: use tenant's first available service
            $detectedService = $availableServices[0]['key'] ?? '';
        }

        if (! $detectedService) {
            return ['service_type' => '', 'found' => [], 'missing' => []];
        }

        // Step 2: Resolve config and extract fields
        $config = $engine->resolve($tenant, $detectedService);
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
            return ['service_type' => $detectedService, 'found' => [], 'missing' => $requiredFields];
        }

        $aiText = $response->text ?? '{}';
        $extractor = new StructuredExtractor;
        $found = $extractor->extract($aiText, $fieldDefs);

        $foundSimple = [];
        foreach ($found as $key => $data) {
            $foundSimple[$key] = $data['value'];
        }

        $missing = array_values(array_diff($requiredFields, array_keys($foundSimple)));

        return ['service_type' => $detectedService, 'found' => $foundSimple, 'missing' => $missing];
    }

    /**
     * Ask AI to detect which service the email is about.
     *
     * @param  array<int, array{key:string, name:string}>  $availableServices
     */
    private function detectServiceFromEmail(string $text, array $availableServices): ?string
    {
        if (empty($availableServices)) {
            return null;
        }

        $options = implode(', ', array_map(fn (array $s): string => "{$s['key']} ({$s['name']})", $availableServices));

        try {
            $response = agent(
                instructions: "És um classificador de serviços. Determina qual serviço corresponde ao email. Responde APENAS com a chave do serviço (ex: \"roof_repair\"). Opções: {$options}. Se não tiveres certeza, responde \"unknown\".",
                messages: [],
            )->prompt("Email do cliente:\n\n{$text}");

            $detected = trim($response->text ?? '');

            // Validate against available service keys
            foreach ($availableServices as $service) {
                if ($service['key'] === $detected) {
                    return $detected;
                }
            }

            return null;
        } catch (\Exception $e) {
            return null;
        }
    }
}
