<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Filament\Widgets\RecentLeadsTable;
use App\Filament\Widgets\RecentMissedCallsTable;
use App\Filament\Widgets\TenantStatsOverview;
use App\Models\ShortLink;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Pages\Page;

class TenantDashboard extends Page
{
    protected static ?string $title = 'Dashboard';

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-home';

    protected string $view = 'filament.pages.tenant-dashboard';

    public ?string $shortLinkUrl = null;

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
        return [
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
}
