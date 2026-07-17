<?php

declare(strict_types=1);

namespace App\Filament\Resources\TenantResource\Pages;

use App\Filament\Resources\TenantResource;
use App\Models\Tenant;
use App\Services\IndustryConfigEngine;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Text;
use Filament\Schemas\Schema;

class ManageServiceConfig extends EditRecord
{
    protected static string $resource = TenantResource::class;

    protected static ?string $title = 'Configuração de Serviços';

    protected static ?string $breadcrumb = 'Serviços';

    public static function canAccess(array $parameters = []): bool
    {
        $user = auth()->user();
        if (! $user) {
            return false;
        }

        // Super-admin can access any tenant
        if ($user->isSuperAdmin()) {
            return true;
        }

        // Tenant users can only access their own config
        $recordId = $parameters['record'] ?? null;

        if ($recordId instanceof Tenant) {
            $recordId = $recordId->id;
        }

        return $recordId && (int) $recordId === $user->tenant_id;
    }

    public function form(Schema $schema): Schema
    {
        $tenant = $this->getRecord();
        $engine = app(IndustryConfigEngine::class);
        $services = $engine->getAvailableServices($tenant);

        $tabs = [];
        foreach ($services as $service) {
            $key = $service['key'];
            $fullConfig = $engine->loadServiceConfig($key);
            if (! $fullConfig) {
                continue;
            }

            $allFields = $this->getAllFieldDefinitions($engine, $tenant, $key);
            $fieldDescriptions = $this->buildFieldDescriptions($allFields);
            $resolvedForDisplay = $engine->resolve($tenant, $key);
            $fieldOptions = $resolvedForDisplay['locales'][$tenant->locale ?? 'pt']['field_options'] ?? [];
            $conditionalFields = $fullConfig['conditional_fields'] ?? [];

            $tabs[] = Tabs\Tab::make($key)
                ->label(($service['icon'] ?? '').' '.$service['name'])
                ->schema([
                    Section::make('Campos Base')
                        ->description('Ative/desative campos e defina se são obrigatórios ou opcionais.')
                        ->schema([
                            Grid::make(3)
                                ->schema($this->buildBaseFieldCards("svc.{$key}", $allFields, $fieldDescriptions, $fieldOptions)),
                        ]),

                    Section::make('Campos Condicionais')
                        ->description('Campos que só aparecem quando certas condições se verificam.')
                        ->collapsible()
                        ->schema([
                            Grid::make(3)
                                ->schema($this->buildConditionalFieldCards("svc.{$key}.conditional_fields", $conditionalFields, $fieldOptions)),
                        ]),

                    Section::make('Regras Condicionais')
                        ->description('Quando certas condições se verificam, exigir campos extra.')
                        ->collapsible()
                        ->schema([
                            Repeater::make("svc.{$key}.conditional_requirements")
                                ->label('')
                                ->addActionLabel('Adicionar regra condicional')
                                ->schema([
                                    Fieldset::make('Condições (todas têm de ser verdade)')
                                        ->schema([
                                            Repeater::make('when_list')
                                                ->label('')
                                                ->addActionLabel('Adicionar condição')
                                                ->schema([
                                                    Select::make('field')
                                                        ->label('Campo')
                                                        ->options($this->buildSelectFieldOptions($allFields))
                                                        ->live()
                                                        ->afterStateUpdated(fn ($set) => $set('values', []))
                                                        ->required(),
                                                    Select::make('values')
                                                        ->label('Valor(es)')
                                                        ->options(fn ($get) => $this->getFieldValues($allFields, $get('field'), $fieldOptions))
                                                        ->multiple()
                                                        ->required(),
                                                ])
                                                ->defaultItems(1)
                                                ->columns(2)
                                                ->columnSpanFull(),
                                        ]),
                                    CheckboxList::make('require')
                                        ->label('Exigir também estes campos')
                                        ->options($this->buildNotRequiredOptions($allFields, $resolvedForDisplay))
                                        ->descriptions($fieldDescriptions)
                                        ->columns(2),
                                ])
                                ->collapsible()
                                ->itemLabel(fn (array $state): string => $this->buildRuleLabel($state, $allFields, $fieldOptions)),
                        ]),

                    Section::make('Mensagens')
                        ->description('Personalizar textos para este tenant.')
                        ->schema([
                            TextInput::make("svc.{$key}.greeting_message")
                                ->label('Mensagem de saudação')
                                ->placeholder($fullConfig['locales'][$tenant->locale ?? 'pt']['ai_prompt']['greeting_message'] ?? '')
                                ->maxLength(500),
                            Repeater::make("svc.{$key}.field_prompts")
                                ->label('Personalizar perguntas por campo')
                                ->addActionLabel('Personalizar pergunta')
                                ->schema([
                                    Select::make('field')
                                        ->label('Campo')
                                        ->options($this->buildFieldSelectOptions($allFields))
                                        ->live()
                                        ->afterStateUpdated(function (string $state, $set) use ($resolvedForDisplay, $tenant) {
                                            $locale = $tenant->locale ?? 'pt';
                                            $set('prompt', $resolvedForDisplay['locales'][$locale]['field_prompts'][$state] ?? '');
                                        })
                                        ->required(),
                                    TextInput::make('prompt')
                                        ->label('Texto da pergunta')
                                        ->required()
                                        ->maxLength(500),
                                ])
                                ->columns(2)
                                ->collapsible(),
                        ]),
                ]);
        }

        return $schema->components([
            Tabs::make('Serviços')->tabs($tabs)->contained(false),
        ])->columns(1);
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $tenant = $this->getRecord();
        $engine = app(IndustryConfigEngine::class);
        $saved = $tenant->service_config ?? [];

        // Ensure every enabled service has an entry with industry defaults pre-filled
        foreach ($engine->getAvailableServices($tenant) as $service) {
            $key = $service['key'];
            if (! isset($saved[$key])) {
                $saved[$key] = [];
            }
            $svcData = &$saved[$key];

            // Pre-fill base_fields with industry defaults (active + required flags)
            if (! isset($svcData['base_fields'])) {
                $svcData['base_fields'] = [];
            }
            $resolved = $engine->resolve($tenant, $key);
            $industryRequired = $resolved['required_fields'] ?? [];
            $industryOptional = $resolved['optional_fields'] ?? [];
            $svcAllFields = $this->getAllFieldDefinitions($engine, $tenant, $key);
            foreach ($svcAllFields as $fKey => $fDef) {
                // Merge old format (required_fields map) if present
                $oldMap = $svcData['required_fields'] ?? [];
                $oldActive = $oldMap[$fKey] ?? null;

                $active = $oldActive ?? in_array($fKey, $industryRequired) || in_array($fKey, $industryOptional);
                $required = in_array($fKey, $industryRequired);

                $svcData['base_fields'][$fKey] = [
                    'active' => $active,
                    'required' => $required,
                ];
            }
            // Clear old format to avoid confusion
            unset($svcData['required_fields']);

            // Pre-fill greeting_message with resolved value (default or tenant override)
            $resolved = $engine->resolve($tenant, $key);
            if (! isset($svcData['greeting_message'])) {
                $locale = $tenant->locale ?? 'pt';
                $svcData['greeting_message'] = $resolved['locales'][$locale]['ai_prompt']['greeting_message'] ?? '';
            }

            // Pre-fill conditional_fields with industry defaults if no tenant override
            $fullConfig = $engine->loadServiceConfig($key);
            $industryConditional = $fullConfig['conditional_fields'] ?? [];
            if (! isset($svcData['conditional_fields'])) {
                $svcData['conditional_fields'] = [];
            }
            foreach ($industryConditional as $cfKey => $cfDef) {
                if (! isset($svcData['conditional_fields'][$cfKey])) {
                    $svcData['conditional_fields'][$cfKey] = [
                        'enabled' => true,
                        'required' => $cfDef['required'] ?? false,
                        'options' => $cfDef['options'] ?? [],
                    ];
                }

                // Always translate options: known keys → labels, unknowns stay
                $opts = $svcData['conditional_fields'][$cfKey]['options'] ?? [];
                if (! empty($opts)) {
                    $resolved = $engine->resolve($tenant, $key);
                    $optLabels = $resolved['locales'][$tenant->locale ?? 'pt']['field_options'][$cfKey] ?? [];
                    $svcData['conditional_fields'][$cfKey]['options'] = array_map(
                        fn ($v) => $optLabels[$v] ?? $v,
                        $opts
                    );
                }
            }

            // Pre-fill field_options for select fields with their translated labels.
            // Shows the full list so tenant can remove defaults or add custom ones.
            // Saved labels become the source of truth; absent = use industry defaults.
            if (! isset($svcData['field_options'])) {
                $svcData['field_options'] = [];
            }
            $resolved = $engine->resolve($tenant, $key);
            $allOptLabels = $resolved['locales'][$tenant->locale ?? 'pt']['field_options'] ?? [];
            $svcAllFields = $this->getAllFieldDefinitions($engine, $tenant, $key);
            foreach ($svcAllFields as $fKey => $fDef) {
                if (($fDef['type'] ?? 'text') !== 'select') {
                    continue;
                }
                $optLabels = $allOptLabels[$fKey] ?? [];
                if (! isset($svcData['field_options'][$fKey])) {
                    // Pre-fill with translated labels from the industry default
                    $svcData['field_options'][$fKey] = array_values($optLabels);
                }
            }

            // conditional_requirements: stored → UI format
            if (! empty($svcData['conditional_requirements'])) {
                $rules = [];
                foreach ($svcData['conditional_requirements'] as $rule) {
                    $whenList = [];
                    foreach ($rule['when'] as $field => $values) {
                        $whenList[] = ['field' => $field, 'values' => (array) $values];
                    }
                    $rules[] = ['when_list' => $whenList, 'require' => $rule['require']];
                }
                $svcData['conditional_requirements'] = $rules;
            }

            // field_prompts: map → repeater format
            if (! empty($svcData['field_prompts'])) {
                $items = [];
                foreach ($svcData['field_prompts'] as $field => $prompt) {
                    $items[] = ['field' => $field, 'prompt' => $prompt];
                }
                $svcData['field_prompts'] = $items;
            }
        }

        $data['svc'] = $saved;

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $svc = $data['svc'] ?? [];

        foreach ($svc as $key => &$svcData) {
            $svcData = array_filter($svcData, fn ($v) => $v !== null && $v !== '' && $v !== []);

            // base_fields: { active, required } map → required_fields[] + optional_fields[]
            if (! empty($svcData['base_fields']) && is_array($svcData['base_fields'])) {
                $required = [];
                $optional = [];
                foreach ($svcData['base_fields'] as $fKey => $state) {
                    if (empty($state['active'])) {
                        continue;
                    }
                    if (! empty($state['required'])) {
                        $required[] = $fKey;
                    } else {
                        $optional[] = $fKey;
                    }
                }
                $svcData['required_fields'] = $required;
                $svcData['optional_fields'] = $optional;
            }
            unset($svcData['base_fields']);

            // conditional_requirements: UI → stored format
            if (! empty($svcData['conditional_requirements'])) {
                $rules = [];
                foreach ($svcData['conditional_requirements'] as $rule) {
                    if (empty($rule['when_list']) || empty($rule['require'])) {
                        continue;
                    }
                    $when = [];
                    foreach ($rule['when_list'] as $cond) {
                        if (! empty($cond['field']) && ! empty($cond['values'])) {
                            $when[$cond['field']] = count($cond['values']) === 1
                                ? $cond['values'][0]
                                : $cond['values'];
                        }
                    }
                    if (! empty($when)) {
                        $rules[] = ['when' => $when, 'require' => $rule['require']];
                    }
                }
                $svcData['conditional_requirements'] = $rules;
            }

            // field_prompts: repeater → map format
            if (! empty($svcData['field_prompts'])) {
                $map = [];
                foreach ($svcData['field_prompts'] as $item) {
                    if (! empty($item['field']) && ! empty($item['prompt'])) {
                        $map[$item['field']] = $item['prompt'];
                    }
                }
                $svcData['field_prompts'] = $map;
            }

            // conditional_fields options: known labels → keys, unknowns stay
            if (! empty($svcData['conditional_fields'])) {
                $fullConfig = app(IndustryConfigEngine::class)->loadServiceConfig($key);
                $fieldOptions = $fullConfig['locales']['pt']['field_options'] ?? [];
                foreach ($svcData['conditional_fields'] as $cfKey => &$cfData) {
                    if (! empty($cfData['options'])) {
                        $optLabels = $fieldOptions[$cfKey] ?? [];
                        $reverseMap = array_flip($optLabels);
                        $cfData['options'] = array_map(
                            fn ($label) => $reverseMap[$label] ?? $label,
                            $cfData['options']
                        );
                    }
                }
            }

            // field_options: already stored as keys, just clean up empty arrays
            if (! empty($svcData['field_options'])) {
                $svcData['field_options'] = array_filter($svcData['field_options'], fn ($opts) => ! empty($opts));
                if (empty($svcData['field_options'])) {
                    unset($svcData['field_options']);
                }
            }
        }

        $this->getRecord()->update(['service_config' => $svc]);
        unset($data['svc']);

        return $data;
    }

    protected function afterSave(): void
    {
        Notification::make()
            ->title('Configuração guardada!')
            ->success()
            ->send();
    }

    // ─── Helpers ────────────────────────────────────────────────

    private function getAllFieldDefinitions(IndustryConfigEngine $engine, $tenant, string $serviceKey): array
    {
        $base = $engine->loadIndustryBase($tenant);
        $service = $engine->loadServiceConfig($serviceKey);

        $merged = array_merge(
            $base['field_definitions'] ?? [],
            $service['field_definitions'] ?? []
        );

        // MySQL JSON columns don't preserve key order, so sort explicitly.
        return $this->sortFields($merged);
    }

    /**
     * Sort fields: shared fields in conversation order, then service-specific ones.
     */
    private function sortFields(array $fields): array
    {
        $baseOrder = ['contact_name', 'phone', 'email', 'property_address', 'postal_code', 'notes'];

        $ordered = [];
        foreach ($baseOrder as $key) {
            if (isset($fields[$key])) {
                $ordered[$key] = $fields[$key];
                unset($fields[$key]);
            }
        }

        foreach ($fields as $key => $def) {
            $ordered[$key] = $def;
        }

        return $ordered;
    }

    private function buildFieldOptions(array $allFields): array
    {
        return $this->buildLabeledOptions($allFields);
    }

    private function buildFieldSelectOptions(array $allFields): array
    {
        return $this->buildLabeledOptions($allFields);
    }

    /**
     * Only select-type fields (for condition builders).
     */
    private function buildSelectFieldOptions(array $allFields): array
    {
        $options = [];
        foreach ($allFields as $fieldKey => $def) {
            if (($def['type'] ?? 'text') === 'select') {
                $options[$fieldKey] = $this->fieldLabel($fieldKey);
            }
        }

        return $options;
    }

    /**
     * Fields that are NOT already required (for "exigir também" list).
     */
    private function buildNotRequiredOptions(array $allFields, array $resolvedConfig): array
    {
        $alreadyRequired = $resolvedConfig['required_fields'] ?? [];

        return array_filter(
            $this->buildLabeledOptions($allFields),
            fn ($label, $key) => ! in_array($key, $alreadyRequired, true),
            ARRAY_FILTER_USE_BOTH
        );
    }

    private function buildLabeledOptions(array $allFields): array
    {
        $options = [];
        foreach ($allFields as $fieldKey => $def) {
            $options[$fieldKey] = $this->fieldLabel($fieldKey);
        }

        return $options;
    }

    private function buildFieldDescriptions(array $allFields): array
    {
        $descriptions = [];
        foreach ($allFields as $fieldKey => $def) {
            $desc = __('fields.descriptions.'.$fieldKey);
            if ($desc !== 'fields.descriptions.'.$fieldKey) {
                $descriptions[$fieldKey] = $desc;
            }
        }

        return $descriptions;
    }

    private function buildConditionalFieldCards(string $statePath, array $conditionalFields, array $fieldOptions): array
    {
        if (empty($conditionalFields)) {
            return [];
        }

        $cards = [];
        foreach ($conditionalFields as $fieldKey => $def) {
            $label = $this->fieldLabel($fieldKey);
            $trigger = $this->formatTrigger($def['when'] ?? [], $fieldOptions);

            $schema = [
                Toggle::make("{$statePath}.{$fieldKey}.enabled")
                    ->label('Ativo')
                    ->default(true)
                    ->inline(false),
                Toggle::make("{$statePath}.{$fieldKey}.required")
                    ->label('Obrigatório')
                    ->default($def['required'] ?? false)
                    ->inline(false),
            ];

            if ($trigger) {
                $schema[] = Text::make("{$fieldKey}_trigger")
                    ->content($trigger)
                    ->color('gray')
                    ->size('sm')
                    ->columnSpanFull();
            }

            // For select fields, allow editing options
            if (($def['type'] ?? 'text') === 'select') {
                $schema[] = TagsInput::make("{$statePath}.{$fieldKey}.options")
                    ->label('Opções')
                    ->placeholder('Adicionar opção...')
                    ->columnSpanFull();
            }

            $cards[] = Fieldset::make($fieldKey)
                ->label($label)
                ->columns(2)
                ->extraAttributes([
                    'x-data' => "{
                        enabled: \$wire.\$entangle('data.{$statePath}.{$fieldKey}.enabled'),
                        required: \$wire.\$entangle('data.{$statePath}.{$fieldKey}.required'),
                    }",
                    'x-init' => "\$watch('required', v => { if (v && !enabled) { enabled = true; } })",
                ])
                ->schema($schema);
        }

        return $cards;
    }

    private function buildBaseFieldCards(string $svcPath, array $allFields, array $descriptions, array $fieldOptions): array
    {
        $cards = [];
        foreach ($allFields as $fieldKey => $def) {
            $label = $this->fieldLabel($fieldKey);
            $desc = $descriptions[$fieldKey] ?? null;

            $activeToggle = Toggle::make("{$svcPath}.base_fields.{$fieldKey}.active")
                ->label($label)
                ->inline(false);

            if ($desc) {
                $activeToggle->hintIcon('heroicon-o-question-mark-circle')
                    ->hintIconTooltip($desc);
            }

            $requiredToggle = Toggle::make("{$svcPath}.base_fields.{$fieldKey}.required")
                ->label('Obrigatório')
                ->inline(false);

            $schema = [$activeToggle, $requiredToggle];

            // For select fields, show options editor in the same card (only when active)
            if (($def['type'] ?? 'text') === 'select') {
                $schema[] = TagsInput::make("{$svcPath}.field_options.{$fieldKey}")
                    ->label('Opções')
                    ->placeholder('Adicionar opção...')
                    ->extraAttributes([
                        'x-data' => "{ show: \$wire.\$entangle('data.{$svcPath}.base_fields.{$fieldKey}.active') }",
                        'x-show' => 'show',
                    ])
                    ->columnSpanFull();
            }

            $cards[] = Fieldset::make($fieldKey)
                ->label($label)
                ->columns(2)
                ->extraAttributes([
                    'x-data' => "{
                        active: \$wire.\$entangle('data.{$svcPath}.base_fields.{$fieldKey}.active'),
                        required: \$wire.\$entangle('data.{$svcPath}.base_fields.{$fieldKey}.required'),
                    }",
                    'x-init' => "\$watch('required', v => { if (v && !active) { active = true; } })",
                ])
                ->schema($schema);
        }

        return $cards;
    }

    private function formatTrigger(array $when, array $fieldOptions = []): string
    {
        $parts = [];
        foreach ($when as $field => $values) {
            $fieldLabel = $this->fieldLabel($field);
            $opts = $fieldOptions[$field] ?? [];
            $valLabels = implode(' ou ', array_map(fn ($v) => $opts[$v] ?? $v, (array) $values));
            $parts[] = "{$fieldLabel} = {$valLabels}";
        }

        return implode(' E ', $parts);
    }

    private function fieldLabel(string $fieldKey): string
    {
        return trans('fields.labels.'.$fieldKey) ?: $fieldKey;
    }

    private function getFieldValues(array $allFields, ?string $fieldKey, array $fieldOptions = []): array
    {
        if (! $fieldKey || ! isset($allFields[$fieldKey])) {
            return [];
        }

        $def = $allFields[$fieldKey];

        if (($def['type'] ?? 'text') === 'select' && ! empty($def['options'])) {
            $labels = $fieldOptions[$fieldKey] ?? [];

            return array_combine(
                $def['options'],
                array_map(fn ($v) => $labels[$v] ?? $v, $def['options'])
            );
        }

        return [];
    }

    private function buildRuleLabel(array $state, array $allFields, array $fieldOptions = []): string
    {
        $parts = [];
        foreach ($state['when_list'] ?? [] as $cond) {
            if (! empty($cond['field'])) {
                $fieldLabel = $this->fieldLabel($cond['field']);
                $opts = $fieldOptions[$cond['field']] ?? [];
                $vals = array_map(fn ($v) => $opts[$v] ?? $v, (array) ($cond['values'] ?? []));
                $parts[] = $fieldLabel.' = '.implode(' ou ', $vals);
            }
        }

        return empty($parts) ? 'Nova regra' : 'Se '.implode(' E ', $parts);
    }
}
