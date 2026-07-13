<?php

declare(strict_types=1);

namespace App\Filament\Resources\TenantResource\RelationManagers;

use App\Models\TenantEmailAccount;
use BackedEnum;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Validation\Rules\Unique;

class EmailAccountsRelationManager extends RelationManager
{
    protected static string $relationship = 'emailAccounts';

    protected static ?string $title = 'Contas de Email';

    protected static BackedEnum|string|null $icon = Heroicon::OutlinedEnvelope;

    public function form(Schema $schema): Schema
    {
        return $schema->schema([
            Select::make('provider')
                ->label('Fornecedor')
                ->options([
                    'google' => 'Google (Gmail)',
                    'microsoft' => 'Microsoft (Outlook)',
                    'custom' => 'Outro / Customizado',
                ])
                ->required()
                ->live()
                ->afterStateUpdated(function ($state, callable $set) {
                    if (! $state) {
                        return;
                    }
                    $set('imap_config', TenantEmailAccount::defaultImapConfig($state));
                    $set('smtp_config', TenantEmailAccount::defaultSmtpConfig($state));
                }),

            TextInput::make('email')
                ->label('Email')
                ->email()
                ->required()
                ->unique(
                    table: 'tenant_email_accounts',
                    column: 'email',
                    ignoreRecord: true,
                    modifyRuleUsing: fn (Unique $rule, callable $get) => $rule->where('tenant_id', $get('tenant_id')),
                ),

            TextInput::make('name')
                ->label('Nome de exibição')
                ->maxLength(255),

            TextInput::make('app_password')
                ->label('App Password')
                ->password()
                ->required()
                ->helperText('Palavra-passe de aplicação gerada nas definições da conta Google/Microsoft.')
                ->dehydrateStateUsing(fn (?string $state) => $state ? Crypt::encryptString($state) : null),

            TextInput::make('watch_folder')
                ->label('Pasta para novos leads')
                ->helperText('IMAP folder name (ex: Leads, [Gmail]/Orçamentos). Emails nesta pasta podem gerar novos leads automaticamente. A caixa de entrada é sempre verificada para corresponder emails de leads existentes.')
                ->maxLength(100),

            Toggle::make('auto_create_leads')
                ->label('Criar leads automaticamente')
                ->helperText('Quando ativo, emails de remetentes desconhecidos na pasta acima criam novos leads. Emails na caixa de entrada NUNCA geram novos leads — apenas correspondem a leads existentes.'),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('provider')
                    ->label('Fornecedor')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'google' => '🔵 Google',
                        'microsoft' => '🔷 Microsoft',
                        'custom' => '⚙️ Custom',
                        default => $state,
                    }),

                TextColumn::make('email')
                    ->label('Email')
                    ->searchable(),

                IconColumn::make('status')
                    ->label('Estado')
                    ->icon(fn (string $state): string => match ($state) {
                        'active' => 'heroicon-o-check-circle',
                        'error' => 'heroicon-o-exclamation-circle',
                        'disconnected' => 'heroicon-o-x-circle',
                        default => 'heroicon-o-question-mark-circle',
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'active' => 'success',
                        'error' => 'danger',
                        'disconnected' => 'warning',
                        default => 'gray',
                    }),

                TextColumn::make('last_synced_at')
                    ->label('Última sincronização')
                    ->dateTime('d/m/Y H:i')
                    ->placeholder('Nunca'),

                TextColumn::make('watch_folder')
                    ->label('Pasta vigiada')
                    ->placeholder('Inbox')
                    ->toggleable(isToggledHiddenByDefault: true),

                IconColumn::make('auto_create_leads')
                    ->label('Auto-criar leads')
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Conectar conta')
                    ->icon('heroicon-o-plus')
                    ->modalWidth('lg')
                    ->createAnother(false),
            ])
            ->recordActions([
                EditAction::make()->modalWidth('lg'),
                DeleteAction::make()
                    ->label('Desconectar')
                    ->requiresConfirmation(),
            ]);
    }
}
