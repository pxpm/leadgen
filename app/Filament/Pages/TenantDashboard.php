<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Filament\Widgets\RecentLeadsTable;
use App\Filament\Widgets\RecentMissedCallsTable;
use App\Filament\Widgets\TenantStatsOverview;
use BackedEnum;
use Filament\Pages\Page;

class TenantDashboard extends Page
{
    protected static ?string $title = 'Dashboard';

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-home';

    protected string $view = 'filament.pages.tenant-dashboard';

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
}
