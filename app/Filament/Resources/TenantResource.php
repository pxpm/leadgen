<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\TenantResource\Pages;
use App\Filament\Resources\TenantResource\RelationManagers\EmailAccountsRelationManager;
use App\Filament\Resources\TenantResource\RelationManagers\SubscriptionsRelationManager;
use App\Models\Tenant;
use App\Rules\IndustriesWithinPlanLimit;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class TenantResource extends Resource
{
    protected static ?string $model = Tenant::class;

    protected static BackedEnum|string|null $navigationIcon = Heroicon::OutlinedBuildingOffice;

    protected static ?string $navigationLabel = 'Tenants';

    protected static ?string $modelLabel = 'Tenant';

    protected static ?string $pluralModelLabel = 'Tenants';

    public static function canAccess(array $parameters = []): bool
    {
        return true; // Page-level restrictions handle access control
    }

    public static function shouldRegisterNavigation(): bool
    {
        $user = auth()->user();

        if (! $user) {
            return false;
        }

        // Hide Tenants menu while impersonating a tenant
        if ($user->isSuperAdmin() && request()->cookie('impersonating_tenant_id')) {
            return false;
        }

        return $user->isSuperAdmin();
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Informação do Tenant')
                ->schema([
                    TextInput::make('name')
                        ->label('Nome')
                        ->required()
                        ->maxLength(255),

                    TextInput::make('slug')
                        ->label('Slug')
                        ->required()
                        ->maxLength(255)
                        ->unique(ignoreRecord: true),

                    TextInput::make('locale')
                        ->label('Idioma')
                        ->required()
                        ->maxLength(5)
                        ->default('pt'),

                    Select::make('industries')
                        ->label('Indústrias')
                        ->relationship('industries', 'name')
                        ->multiple()
                        ->searchable()
                        ->preload()
                        ->required()
                        ->rules([
                            fn (?Tenant $record) => $record?->plan
                                ? new IndustriesWithinPlanLimit($record->plan)
                                : null,
                        ]),

                    Placeholder::make('current_plan')
                        ->label('Plano Atual')
                        ->content(fn (?Tenant $record) => $record?->plan?->name ?? '—'),
                ])
                ->columns(2),

            Section::make('Integrações')
                ->schema([
                    TextInput::make('stripe_customer_id')
                        ->label('Stripe Customer ID')
                        ->maxLength(255),

                    TextInput::make('twilio_phone_number')
                        ->label('Número Twilio')
                        ->maxLength(50),

                    TextInput::make('twilio_phone_sid')
                        ->label('Twilio Phone SID')
                        ->maxLength(255),
                ])
                ->columns(2),

            Section::make('Configurações Avançadas (JSON)')
                ->schema([
                    Textarea::make('branding_config')
                        ->label('Branding Config')
                        ->rows(4)
                        ->formatStateUsing(fn (?array $state) => $state ? json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : '')
                        ->dehydrateStateUsing(fn (?string $state) => $state ? json_decode($state, true) : null),

                    Textarea::make('notification_config')
                        ->label('Notification Config')
                        ->rows(4)
                        ->formatStateUsing(fn (?array $state) => $state ? json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : '')
                        ->dehydrateStateUsing(fn (?string $state) => $state ? json_decode($state, true) : null),

                    Textarea::make('qualification_overrides')
                        ->label('Qualification Overrides')
                        ->rows(4)
                        ->formatStateUsing(fn (?array $state) => $state ? json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : '')
                        ->dehydrateStateUsing(fn (?string $state) => $state ? json_decode($state, true) : null),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')->label('#')->sortable(),
                TextColumn::make('name')->label('Nome')->searchable()->sortable(),
                TextColumn::make('slug')->label('Slug')->searchable(),
                TextColumn::make('locale')->label('Idioma'),
                TextColumn::make('subscriptions.plan.name')->label('Plano')->badge(),
                TextColumn::make('created_at')->label('Criado')->dateTime('d/m/Y'),
            ])
            ->actions([
                Action::make('impersonate')
                    ->label('Assumir')
                    ->icon(Heroicon::OutlinedUserCircle)
                    ->color('warning')
                    ->tooltip('Assumir identidade deste tenant')
                    ->visible(fn (): bool => auth()->user()?->isSuperAdmin() ?? false)
                    ->url(fn (Tenant $record): string => route('impersonation.start', ['tenant' => $record])),
                Action::make('config')
                    ->icon(Heroicon::OutlinedCog)
                    ->tooltip('Configurar Serviços')
                    ->url(fn (Tenant $record) => TenantResource::getUrl('service-config', ['record' => $record])),
            ])
            ->defaultSort('id', 'asc');
    }

    public static function getRelations(): array
    {
        return [
            SubscriptionsRelationManager::class,
            EmailAccountsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTenants::route('/'),
            'create' => Pages\CreateTenant::route('/create'),
            'view' => Pages\ViewTenant::route('/{record}'),
            'edit' => Pages\EditTenant::route('/{record}/edit'),
            'service-config' => Pages\ManageServiceConfig::route('/{record}/service-config'),
        ];
    }
}
