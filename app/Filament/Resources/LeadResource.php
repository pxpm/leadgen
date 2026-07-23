<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enums\LeadSource;
use App\Enums\LeadStatus;
use App\Filament\Resources\LeadResource\Pages;
use App\Filament\Resources\LeadResource\RelationManagers\EmailMessagesRelationManager;
use App\Models\Lead;
use App\Services\IndustryConfigEngine;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Infolists\Components\Tabs;
use Filament\Infolists\Components\Tabs\Tab;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class LeadResource extends Resource
{
    protected static ?string $model = Lead::class;

    protected static BackedEnum|string|null $navigationIcon = Heroicon::OutlinedInboxArrowDown;

    protected static ?string $navigationLabel = 'Leads';

    protected static ?string $modelLabel = 'Lead';

    protected static ?string $pluralModelLabel = 'Leads';

    public static function shouldRegisterNavigation(): bool
    {
        $user = auth()->user();

        if (! $user) {
            return false;
        }

        // Hide Leads menu while impersonating a tenant
        if ($user->isSuperAdmin() && request()->cookie('impersonating_tenant_id')) {
            return false;
        }

        return $user->isSuperAdmin();
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema->components([
            Section::make(__('admin.lead.section_contact'))->schema([
                TextEntry::make('contact_name')->label(__('admin.lead.contact_name'))->state(fn (Lead $lead) => $lead->fields->where('field_key', 'contact_name')->first()?->field_value ?? '—'),
                TextEntry::make('phone')->label(__('admin.lead.phone'))->state(fn (Lead $lead) => $lead->fields->where('field_key', 'phone')->first()?->field_value ?? '—'),
                TextEntry::make('email')->label(__('admin.lead.email'))->state(fn (Lead $lead) => $lead->fields->where('field_key', 'email')->first()?->field_value ?? '—'),
                TextEntry::make('property_address')->label(__('admin.lead.property_address'))->state(fn (Lead $lead) => $lead->fields->where('field_key', 'property_address')->first()?->field_value ?? '—'),
                TextEntry::make('postal_code')->label(__('admin.lead.postal_code'))->state(fn (Lead $lead) => $lead->fields->where('field_key', 'postal_code')->first()?->field_value ?? '—'),
            ])->columns(2),

            Section::make(__('admin.lead.section_details'))
                ->description(fn (Lead $lead): string => __('admin.lead.column_service').': '.self::getServiceNames($lead))
                ->schema(fn (Lead $lead) => self::buildServiceTabs($lead))
                ->columns(2),

            Section::make(__('admin.lead.section_status'))->schema([
                TextEntry::make('status')->label(__('admin.lead.status'))->state(fn (Lead $lead) => __('admin.lead.status_'.$lead->status->value))->badge(),
                TextEntry::make('source')->label(__('admin.lead.source'))->state(fn (Lead $lead) => $lead->source->value)->badge(),
                TextEntry::make('qualification_score')->label(__('admin.lead.score'))->state(fn (Lead $lead) => $lead->qualification_score ? "{$lead->qualification_score}/10" : '—'),
            ])->columns(2),

            Section::make(__('admin.lead.section_notes'))->schema([
                TextEntry::make('notes')->label(__('admin.lead.additional_notes'))->state(fn (Lead $lead) => $lead->notes ?? '—'),
            ]),
        ]);
    }

    /**
     * Build tabs for each service the lead is interested in.
     *
     * @return array<int, Tabs|Section>
     */
    private static function buildServiceTabs(Lead $lead): array
    {
        $services = $lead->getAllServices();

        // Single service — no tabs needed, just show the fields directly
        if (count($services) <= 1) {
            return self::buildDynamicFieldEntries($lead, $services[0] ?? null);
        }

        // Multiple services — render tabs
        $tabs = [];

        foreach ($services as $serviceKey) {
            $label = self::getServiceLabel($serviceKey, $lead);
            $entries = self::buildDynamicFieldEntries($lead, $serviceKey);

            if (empty($entries)) {
                continue;
            }

            $tabs[] = Tab::make($serviceKey)
                ->label($label)
                ->schema($entries);
        }

        if (empty($tabs)) {
            return self::buildDynamicFieldEntries($lead, $services[0] ?? null);
        }

        return [Tabs::make('services')->tabs($tabs)];
    }

    /**
     * Get all service keys for this lead: primary + pending.
     *
     * @return array<int, string>
     */
    private static function getServiceNames(Lead $lead): string
    {
        $services = $lead->getAllServices();

        return implode(' + ', array_map(
            fn (string $key) => self::getServiceLabel($key, $lead),
            $services
        )) ?: '—';
    }

    /**
     * Get a human-readable label for a service key.
     */
    private static function getServiceLabel(string $serviceKey, Lead $lead): string
    {
        $engine = app(IndustryConfigEngine::class);
        $locale = $lead->tenant->locale ?? 'pt';

        try {
            $config = $engine->loadServiceConfig($serviceKey);

            return $config['locales'][$locale]['name'] ?? $serviceKey;
        } catch (\Throwable) {
            return $serviceKey;
        }
    }

    /**
     * Build TextEntry components dynamically from a specific service's field definitions.
     * Shows only fields that are actually stored on the lead.
     *
     * @return array<int, TextEntry>
     */
    private static function buildDynamicFieldEntries(Lead $lead, ?string $serviceType = null): array
    {
        $engine = app(IndustryConfigEngine::class);
        $locale = $lead->tenant->locale ?? 'pt';

        if ($serviceType) {
            $config = $engine->resolve($lead->tenant, $serviceType);
            // Load raw service config for locale-specific option labels
            $serviceConfig = $engine->loadServiceConfig($serviceType);
        } elseif ($lead->services) {
            $config = $engine->resolve($lead->tenant, $lead->services[0]);
            $serviceConfig = $engine->loadServiceConfig($lead->services[0]);
        } else {
            $config = $engine->loadIndustryBase($lead->tenant);
            $serviceConfig = null;
        }

        $fieldDefs = $config['field_definitions'] ?? [];
        $locale = $lead->tenant->locale ?? 'pt';

        // Shared fields already shown in section_contact — exclude them
        $contactFields = ['contact_name', 'phone', 'email', 'property_address', 'postal_code', 'notes'];

        $entries = [];

        foreach ($fieldDefs as $key => $def) {
            if (in_array($key, $contactFields)) {
                continue;
            }

            $field = $lead->fields->where('field_key', $key)->first();
            $value = $field?->field_value;

            if (! $value) {
                continue;
            }

            // Translate select field values using locale-specific labels from the raw service config
            $displayValue = $value;
            if (($def['type'] ?? 'text') === 'select') {
                $displayValue = $serviceConfig['locales'][$locale]['field_options'][$key][$value]
                    ?? $config['locales'][$locale]['field_options'][$key][$value]
                    ?? $value;
            }

            $entries[] = TextEntry::make($key)
                ->label(self::getFieldLabel($key, $config, $locale))
                ->state($displayValue);
        }

        return $entries;
    }

    /**
     * Get a human-readable label for a field key from the config or fallback to the key itself.
     *
     * @param  array<string, mixed>  $config
     */
    private static function getFieldLabel(string $key, array $config, string $locale): string
    {
        // Try to get the field prompt as a label (it's the question asked in the widget)
        $prompt = $config['locales'][$locale]['ai_prompt']['field_prompts'][$key]
            ?? $config['locales'][$locale]['field_prompts'][$key]
            ?? null;

        if ($prompt) {
            // Truncate long prompts for display
            return mb_strlen($prompt) > 50 ? mb_substr($prompt, 0, 47).'…' : $prompt;
        }

        // Fallback: prettify the key
        return ucfirst(str_replace('_', ' ', $key));
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')->label(__('admin.lead.column_hash'))->sortable(),
                TextColumn::make('services')->label(__('admin.lead.column_service'))->badge()->sortable()->formatStateUsing(fn ($state) => is_array($state) ? ($state[0] ?? null) : $state),
                TextColumn::make('status')->label(__('admin.lead.column_status'))->badge()->color(fn (LeadStatus $state) => match ($state) {
                    LeadStatus::New => 'gray',
                    LeadStatus::InProgress => 'warning',
                    LeadStatus::Qualified => 'success',
                    LeadStatus::Delivered => 'info',
                })->formatStateUsing(fn (LeadStatus $state) => __('admin.lead.status_'.$state->value))->sortable(),
                TextColumn::make('source')->label(__('admin.lead.column_source'))->badge()->sortable(),
                TextColumn::make('qualification_score')->label(__('admin.lead.column_score'))->sortable(),
                TextColumn::make('created_at')->label(__('admin.lead.column_date'))->dateTime('d/m/Y H:i')->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('status')->options(collect(LeadStatus::cases())->mapWithKeys(fn ($s) => [$s->value => $s->value])->toArray()),
                SelectFilter::make('source')->options(collect(LeadSource::cases())->mapWithKeys(fn ($s) => [$s->value => $s->value])->toArray()),
            ])
            ->actions([
                Action::make('mark_delivered')->label(__('admin.lead.delivered_action'))->color('success')->icon(Heroicon::OutlinedCheck)->action(fn (Lead $lead) => $lead->update(['status' => LeadStatus::Delivered, 'delivered_at' => now()])),
            ])
            ->bulkActions([
                BulkActionGroup::make([DeleteBulkAction::make()]),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user = auth()->user();

        // Super admin sees all; tenant users see only their tenant's leads
        if ($user && ! $user->isSuperAdmin() && $user->tenant_id) {
            $query->where('tenant_id', $user->tenant_id);
        }

        return $query;
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLeads::route('/'),
            'view' => Pages\ViewLead::route('/{record}'),
        ];
    }

    public static function getRelations(): array
    {
        return [
            EmailMessagesRelationManager::class,
        ];
    }
}
