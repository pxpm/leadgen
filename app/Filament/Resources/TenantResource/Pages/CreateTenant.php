<?php

declare(strict_types=1);

namespace App\Filament\Resources\TenantResource\Pages;

use App\Filament\Resources\TenantResource;
use App\Models\Plan;
use App\Services\TenantService;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class CreateTenant extends CreateRecord
{
    protected static string $resource = TenantResource::class;

    public static function canAccess(array $parameters = []): bool
    {
        return auth()->user()?->isSuperAdmin() ?? false;
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Informação da Empresa')
                ->schema([
                    TextInput::make('name')
                        ->label('Nome da Empresa')
                        ->required()
                        ->maxLength(255)
                        ->live(onBlur: true)
                        ->afterStateUpdated(fn ($state, callable $set) => $set('slug', Str::slug($state))),

                    TextInput::make('slug')
                        ->label('Slug')
                        ->required()
                        ->maxLength(255)
                        ->unique('tenants', 'slug'),

                    Select::make('locale')
                        ->label('Idioma')
                        ->options([
                            'pt' => 'Português',
                            'en' => 'English',
                        ])
                        ->default('pt')
                        ->required(),

                    Select::make('industry_id')
                        ->label('Indústria')
                        ->relationship('industry', 'name')
                        ->searchable()
                        ->preload()
                        ->required()
                        ->live(),

                    CheckboxList::make('active_services')
                        ->label('Serviços')
                        ->options(function (callable $get) {
                            $industryId = $get('industry_id');
                            if (! $industryId) {
                                return [];
                            }

                            $industry = \App\Models\Industry::find($industryId);
                            if (! $industry) {
                                return [];
                            }

                            $config = require database_path("seeders/data/industries/{$industry->slug}.php");
                            $keys = $config['services'] ?? [];
                            $engine = app(\App\Services\IndustryConfigEngine::class);

                            return collect($keys)->mapWithKeys(function ($key) use ($engine) {
                                $config = $engine->loadServiceConfig($key);

                                return [$key => ($config['icon'] ?? '').' '.($config['locales']['pt']['name'] ?? $key)];
                            })->toArray();
                        })
                        ->columns(2),
                ])
                ->columns(2),

            Section::make('Utilizador Admin')
                ->schema([
                    TextInput::make('admin_name')
                        ->label('Nome')
                        ->required()
                        ->maxLength(255),

                    TextInput::make('admin_email')
                        ->label('Email')
                        ->email()
                        ->required()
                        ->maxLength(255),

                    Toggle::make('send_magic_link')
                        ->label('Enviar magic link')
                        ->helperText('O admin receberá um email com um link para fazer login e definir a password.')
                        ->default(true),
                ])
                ->columns(2),

            Section::make('Subscrição')
                ->schema([
                    Select::make('plan_id')
                        ->label('Plano')
                        ->options(Plan::where('is_active', true)->pluck('name', 'id'))
                        ->searchable()
                        ->preload()
                        ->required(),

                    Select::make('subscription_status')
                        ->label('Estado')
                        ->options([
                            'active' => 'Ativo',
                            'trialing' => 'Trial',
                            'past_due' => 'Pagamento em Atraso',
                            'canceled' => 'Cancelado',
                        ])
                        ->default('active')
                        ->required(),

                    DateTimePicker::make('trial_ends_at')
                        ->label('Fim do Trial')
                        ->nullable(),
                ])
                ->columns(3),
        ]);
    }

    public function create(bool $another = false): void
    {
        $data = $this->form->getState();

        $tenant = app(TenantService::class)->createTenant($data);

        $this->redirect(TenantResource::getUrl('view', ['record' => $tenant]));

        $message = "Tenant '{$tenant->name}' criado.";
        if ($data['send_magic_link'] ?? true) {
            $message .= " Magic link enviado para {$data['admin_email']}.";
        }

        Notification::make()
            ->title('Tenant Criado')
            ->body($message)
            ->success()
            ->send();
    }
}
