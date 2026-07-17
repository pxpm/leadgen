<?php

declare(strict_types=1);

namespace App\Filament\Resources\TenantResource\RelationManagers;

use App\Jobs\SendEmailVerificationJob;
use App\Models\TenantEmailAccount;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
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
                    ->formatStateUsing(function (TenantEmailAccount $record): string {
                        $icon = match ($record->provider) {
                            'google' => '🔵',
                            'microsoft' => '🔷',
                            default => '⚙️',
                        };
                        $type = match ($record->connection_type) {
                            'google_oauth', 'microsoft_oauth' => ' (OAuth)',
                            'imap_password' => ' (App Password)',
                            default => '',
                        };

                        return $icon.' '.ucfirst($record->provider).$type;
                    }),

                TextColumn::make('email')
                    ->label('Email')
                    ->searchable(),

                IconColumn::make('verified_at')
                    ->label('Verificado')
                    ->icon(fn (?string $state): string => $state
                        ? 'heroicon-o-check-badge'
                        : 'heroicon-o-clock')
                    ->color(fn (?string $state): string => $state
                        ? 'success'
                        : 'warning')
                    ->tooltip(fn (?string $state): string => $state
                        ? 'Email verificado'
                        : 'Aguardando verificação'),

                IconColumn::make('status')
                    ->label('Estado')
                    ->icon(fn (string $state): string => match ($state) {
                        'active' => 'heroicon-o-check-circle',
                        'pending_verification' => 'heroicon-o-clock',
                        'error' => 'heroicon-o-exclamation-circle',
                        'disconnected' => 'heroicon-o-x-circle',
                        default => 'heroicon-o-question-mark-circle',
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'active' => 'success',
                        'pending_verification' => 'warning',
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
                Action::make('connectGoogle')
                    ->label('Conectar Gmail')
                    ->icon('heroicon-o-envelope')
                    ->color('primary')
                    ->url(fn () => url('/api/oauth/google/redirect'))
                    ->visible(fn (): bool => ! TenantEmailAccount::where('tenant_id', auth()->user()?->tenant_id)
                        ->where('provider', 'google')
                        ->where('connection_type', 'google_oauth')
                        ->exists()),

                Action::make('connectMicrosoft')
                    ->label('Conectar Outlook')
                    ->icon('heroicon-o-envelope-open')
                    ->color('info')
                    ->url(fn () => url('/api/oauth/microsoft/redirect'))
                    ->visible(fn (): bool => ! TenantEmailAccount::where('tenant_id', auth()->user()?->tenant_id)
                        ->where('provider', 'microsoft')
                        ->where('connection_type', 'microsoft_oauth')
                        ->exists()),

                CreateAction::make()
                    ->label('Adicionar manualmente')
                    ->icon('heroicon-o-plus')
                    ->modalWidth('lg')
                    ->createAnother(false)
                    ->after(function (TenantEmailAccount $record): void {
                        SendEmailVerificationJob::dispatch($record);

                        Notification::make()
                            ->title('Conta criada!')
                            ->body('Enviamos um link de verificação para '.$record->email.'. Clica no link no email para ativares a conta.')
                            ->success()
                            ->send();
                    }),
            ])
            ->recordActions([
                EditAction::make()->modalWidth('lg'),

                Action::make('checkVerification')
                    ->label('Verificar agora')
                    ->icon('heroicon-o-arrow-path')
                    ->visible(fn (TenantEmailAccount $record): bool => $record->isPendingVerification())
                    ->color('gray')
                    ->action(function (TenantEmailAccount $record): void {
                        $record->refresh();

                        if ($record->isVerified()) {
                            Notification::make()
                                ->title('Email verificado!')
                                ->body('A conta '.$record->email.' está agora ativa.')
                                ->success()
                                ->send();
                        } else {
                            Notification::make()
                                ->title('Ainda não verificado')
                                ->body('Clica no link que enviamos para '.$record->email.'. Se não o encontrares, reenvia.')
                                ->warning()
                                ->send();
                        }
                    }),

                Action::make('resendVerification')
                    ->label('Reenviar link')
                    ->icon('heroicon-o-envelope')
                    ->visible(fn (TenantEmailAccount $record): bool => $record->isPendingVerification())
                    ->color('info')
                    ->requiresConfirmation()
                    ->modalHeading('Reenviar link de verificação')
                    ->modalDescription('Um novo link será enviado para '.fn (TenantEmailAccount $record): string => $record->email)
                    ->action(function (TenantEmailAccount $record): void {
                        SendEmailVerificationJob::dispatch($record);

                        Notification::make()
                            ->title('Link reenviado!')
                            ->body('Verifica o teu email '.$record->email.' e clica no link.')
                            ->success()
                            ->send();
                    }),

                DeleteAction::make()
                    ->label('Desconectar')
                    ->requiresConfirmation(),
            ]);
    }
}
