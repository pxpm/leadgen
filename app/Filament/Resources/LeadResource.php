<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enums\LeadSource;
use App\Enums\LeadStatus;
use App\Filament\Resources\LeadResource\Pages;
use App\Models\Lead;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
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
        return auth()->user()?->isSuperAdmin() ?? false;
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Contacto')->schema([
                TextEntry::make('contact_name')->label('Nome')->state(fn (Lead $lead) => $lead->fields->where('field_key', 'contact_name')->first()?->field_value ?? '—'),
                TextEntry::make('phone')->label('Telefone')->state(fn (Lead $lead) => $lead->fields->where('field_key', 'phone')->first()?->field_value ?? '—'),
                TextEntry::make('email')->label('Email')->state(fn (Lead $lead) => $lead->fields->where('field_key', 'email')->first()?->field_value ?? '—'),
                TextEntry::make('property_address')->label('Morada')->state(fn (Lead $lead) => $lead->fields->where('field_key', 'property_address')->first()?->field_value ?? '—'),
                TextEntry::make('postal_code')->label('Código Postal')->state(fn (Lead $lead) => $lead->fields->where('field_key', 'postal_code')->first()?->field_value ?? '—'),
                TextEntry::make('property_type')->label('Tipo de Propriedade')->state(fn (Lead $lead) => self::translatedField($lead, 'property_type')),
            ])->columns(2),
            Section::make('Detalhes')->schema([
                TextEntry::make('problem_type')->label('Tipo de Problema')->state(fn (Lead $lead) => self::translatedField($lead, 'problem_type')),
                TextEntry::make('roof_type')->label('Tipo de Telhado')->state(fn (Lead $lead) => self::translatedField($lead, 'roof_type')),
                TextEntry::make('house_type')->label('Tipo de Moradia')->state(fn (Lead $lead) => self::translatedField($lead, 'house_type')),
                TextEntry::make('urgency')->label('Urgência')->state(fn (Lead $lead) => self::translatedField($lead, 'urgency')),
                TextEntry::make('insurance_claim')->label('Sinistro de Seguro')->state(fn (Lead $lead) => self::translatedField($lead, 'insurance_claim')),
                TextEntry::make('roof_size')->label('Tamanho do Telhado')->state(fn (Lead $lead) => self::translatedField($lead, 'roof_size')),
                TextEntry::make('roof_age')->label('Idade do Telhado')->state(fn (Lead $lead) => self::translatedField($lead, 'roof_age')),
                TextEntry::make('leak_location')->label('Local da Infiltração')->state(fn (Lead $lead) => $lead->fields->where('field_key', 'leak_location')->first()?->field_value ?? '—'),
            ])->columns(2),
            Section::make('Estado')->schema([
                TextEntry::make('status')->label('Estado')->state(fn (Lead $lead) => $lead->status->value)->badge(),
                TextEntry::make('source')->label('Origem')->state(fn (Lead $lead) => $lead->source->value)->badge(),
                TextEntry::make('qualification_score')->label('Pontuação')->state(fn (Lead $lead) => $lead->qualification_score ? "{$lead->qualification_score}/10" : '—'),
            ])->columns(2),
            Section::make('Notas')->schema([
                TextEntry::make('notes')->label('Notas adicionais')->state(fn (Lead $lead) => $lead->notes ?? '—'),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')->label('#')->sortable(),
                TextColumn::make('service_type')->label('Serviço')->badge()->sortable(),
                TextColumn::make('status')->label('Estado')->badge()->color(fn (LeadStatus $state) => match ($state) {
                    LeadStatus::New => 'gray',
                    LeadStatus::InProgress => 'warning',
                    LeadStatus::Qualified => 'success',
                    LeadStatus::Delivered => 'info',
                })->sortable(),
                TextColumn::make('source')->label('Origem')->badge()->sortable(),
                TextColumn::make('qualification_score')->label('Score')->sortable(),
                TextColumn::make('created_at')->label('Data')->dateTime('d/m/Y H:i')->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('status')->options(collect(LeadStatus::cases())->mapWithKeys(fn ($s) => [$s->value => $s->value])->toArray()),
                SelectFilter::make('source')->options(collect(LeadSource::cases())->mapWithKeys(fn ($s) => [$s->value => $s->value])->toArray()),
            ])
            ->actions([
                Action::make('mark_delivered')->label('Entregue')->color('success')->icon(Heroicon::OutlinedCheck)->action(fn (Lead $lead) => $lead->update(['status' => LeadStatus::Delivered, 'delivered_at' => now()])),
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

    private static function translatedField(Lead $lead, string $key): string
    {
        $field = $lead->fields->where('field_key', $key)->first();
        if (! $field?->field_value) {
            return 'Não informado';
        }

        $tenant = $lead->tenant;
        $locale = $tenant->locale ?? 'pt';
        $config = $tenant->industry?->config;

        return $config['locales'][$locale]['field_options'][$key][$field->field_value]
            ?? $field->field_value;
    }
}
