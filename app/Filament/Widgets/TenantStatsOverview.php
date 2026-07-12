<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\Lead;
use App\Models\MissedCall;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class TenantStatsOverview extends StatsOverviewWidget
{
    protected static ?int $sort = 0;

    protected function getStats(): array
    {
        $tenantId = auth()->user()?->tenant_id;

        $totalLeads = Lead::where('tenant_id', $tenantId)->count();
        $qualifiedLeads = Lead::where('tenant_id', $tenantId)->whereNotNull('qualified_at')->count();
        $totalCalls = MissedCall::where('tenant_id', $tenantId)->count();
        $recoveredCalls = MissedCall::where('tenant_id', $tenantId)->where('sms_sent', true)->count();
        $conversionRate = $totalLeads > 0 ? round(($qualifiedLeads / $totalLeads) * 100) : 0;

        return [
            Stat::make('Total de Leads', $totalLeads)
                ->description('Todos os leads')
                ->icon('heroicon-o-user-group')
                ->color('primary'),

            Stat::make('Leads Qualificados', $qualifiedLeads)
                ->description("{$conversionRate}% de conversão")
                ->icon('heroicon-o-check-badge')
                ->color('success'),

            Stat::make('Chamadas Perdidas', $totalCalls)
                ->description('Total de chamadas')
                ->icon('heroicon-o-phone-arrow-down-left')
                ->color('warning'),

            Stat::make('Chamadas Recuperadas', $recoveredCalls)
                ->description($totalCalls > 0 ? round(($recoveredCalls / $totalCalls) * 100).'% recuperação' : '0%')
                ->icon('heroicon-o-arrow-path')
                ->color('info'),
        ];
    }

    /**
     * Only show for non-super-admin users.
     */
    public static function canView(): bool
    {
        return ! auth()->user()?->isSuperAdmin();
    }
}
