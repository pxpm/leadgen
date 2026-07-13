<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Filament\Widgets\RecentLeadsTable;
use App\Filament\Widgets\RecentMissedCallsTable;
use App\Filament\Widgets\TenantStatsOverview;
use App\Models\ShortLink;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Pages\Page;

class TenantDashboard extends Page
{
    protected static ?string $title = 'Dashboard';

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-home';

    protected string $view = 'filament.pages.tenant-dashboard';

    public ?string $shortLinkUrl = null;

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
        return [
            Action::make('generateIntakeLink')
                ->label('Gerar link de qualificação')
                ->icon('heroicon-o-link')
                ->color('primary')
                ->modalHeading('Link de qualificação')
                ->modalDescription('Partilhe este link com potenciais clientes. O link expira em 24 horas.')
                ->form([
                    TextInput::make('url')
                        ->label('URL')
                        ->disabled()
                        ->default(fn () => $this->shortLinkUrl)
                        ->extraAttributes(['class' => 'font-mono text-sm'])
                        ->columnSpanFull(),
                ])
                ->action(function (): void {
                    $tenant = auth()->user()->tenant;

                    $shortLink = ShortLink::create([
                        'hash' => ShortLink::generateHash(),
                        'tenant_id' => $tenant->id,
                        'source' => 'direct_link',
                        'expires_at' => now()->addHours(24),
                    ]);

                    $this->shortLinkUrl = url('/s/'.$shortLink->hash);
                })
                ->extraModalFooterActions([
                    Action::make('copyLink')
                        ->label('Copiar link')
                        ->icon('heroicon-o-clipboard')
                        ->extraAttributes(['onclick' => 'navigator.clipboard.writeText(document.querySelector(\'[name=\"url\"]\').value);this.innerText=\'Copiado!\';setTimeout(()=>this.innerText=\'Copiar link\',2000)']),
                ]),
        ];
    }
}
