<?php

declare(strict_types=1);

namespace App\Filament\Resources\TenantResource\Pages;

use App\Filament\Resources\TenantResource;
use App\Models\Tenant;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ViewTenant extends ViewRecord
{
    protected static string $resource = TenantResource::class;

    public static function canAccess(array $parameters = []): bool
    {
        return auth()->user()?->isSuperAdmin() ?? false;
    }

    public function infolist(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Informação do Tenant')
                ->schema([
                    TextEntry::make('name')
                        ->label('Nome'),

                    TextEntry::make('slug')
                        ->label('Slug'),

                    TextEntry::make('locale')
                        ->label('Idioma'),

                    TextEntry::make('industry.name')
                        ->label('Indústria'),

                    TextEntry::make('plan.name')
                        ->label('Plano')
                        ->badge(),
                ])
                ->columns(2),

            Section::make('Utilização')
                ->schema([
                    TextEntry::make('leads_count')
                        ->label('Total de Leads')
                        ->state(fn (Tenant $record) => $record->leads()->count()),

                    TextEntry::make('qualified_leads_count')
                        ->label('Leads Qualificados')
                        ->state(fn (Tenant $record) => $record->leads()->whereNotNull('qualified_at')->count()),

                    TextEntry::make('delivered_leads_count')
                        ->label('Leads Entregues')
                        ->state(fn (Tenant $record) => $record->leads()->whereNotNull('delivered_at')->count()),

                    TextEntry::make('widget_leads_count')
                        ->label('Leads via Widget')
                        ->state(fn (Tenant $record) => $record->leads()->where('source', 'widget')->count()),

                    TextEntry::make('missed_calls_count')
                        ->label('Chamadas Perdidas')
                        ->state(fn (Tenant $record) => $record->missedCalls()->count()),

                    TextEntry::make('recovered_calls_count')
                        ->label('Chamadas Recuperadas')
                        ->state(fn (Tenant $record) => $record->missedCalls()->where('sms_sent', true)->count()),

                    TextEntry::make('sms_count')
                        ->label('SMS Enviados')
                        ->state(fn (Tenant $record) => $record->notifications()->where('channel', 'sms')->count()),

                    TextEntry::make('email_count')
                        ->label('Emails Enviados')
                        ->state(fn (Tenant $record) => $record->notifications()->where('channel', 'email')->count()),

                    TextEntry::make('users_count')
                        ->label('Utilizadores')
                        ->state(fn (Tenant $record) => $record->users()->count()),
                ])
                ->columns(3),

            Section::make('Integrações')
                ->schema([
                    TextEntry::make('stripe_customer_id')
                        ->label('Stripe Customer ID')
                        ->default('—'),

                    TextEntry::make('twilio_phone_number')
                        ->label('Número Twilio')
                        ->default('—'),

                    TextEntry::make('twilio_phone_sid')
                        ->label('Twilio Phone SID')
                        ->default('—'),
                ])
                ->columns(2),

            Section::make('Datas')
                ->schema([
                    TextEntry::make('created_at')
                        ->label('Criado em')
                        ->dateTime('d/m/Y H:i'),

                    TextEntry::make('updated_at')
                        ->label('Atualizado em')
                        ->dateTime('d/m/Y H:i'),
                ])
                ->columns(2),
        ]);
    }
}
