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
        $plan = tenant()?->plan;

        $totalLeads = Lead::where('tenant_id', $tenantId)->count();
        $qualifiedLeads = Lead::where('tenant_id', $tenantId)->whereNotNull('qualified_at')->count();
        $conversionRate = $totalLeads > 0 ? round(($qualifiedLeads / $totalLeads) * 100) : 0;

        $stats = [
            Stat::make(__('admin.stats.total_leads'), $totalLeads)
                ->description(__('admin.stats.total_leads_desc'))
                ->icon('heroicon-o-user-group')
                ->color('primary'),

            Stat::make(__('admin.stats.qualified_leads'), $qualifiedLeads)
                ->description(__('admin.stats.qualified_leads_desc', ['rate' => $conversionRate]))
                ->icon('heroicon-o-check-badge')
                ->color('success'),
        ];

        // Only show missed call stats if the plan includes call recovery
        if ($plan && ($plan->limits['recovery_call'] ?? false)) {
            $totalCalls = MissedCall::where('tenant_id', $tenantId)->count();
            $recoveredCalls = MissedCall::where('tenant_id', $tenantId)->where('sms_sent', true)->count();

            $stats[] = Stat::make(__('admin.stats.missed_calls'), $totalCalls)
                ->description(__('admin.stats.missed_calls_desc'))
                ->icon('heroicon-o-phone-arrow-down-left')
                ->color('warning');

            $stats[] = Stat::make(__('admin.stats.recovered_calls'), $recoveredCalls)
                ->description($totalCalls > 0
                    ? __('admin.stats.recovered_calls_desc', ['rate' => round(($recoveredCalls / $totalCalls) * 100)])
                    : __('admin.stats.recovered_calls_desc_zero'))
                ->icon('heroicon-o-arrow-path')
                ->color('info');
        }

        return $stats;
    }

    /**
     * Only show for non-super-admin users.
     */
    public static function canView(): bool
    {
        $user = auth()->user();

        if (! $user) {
            return false;
        }

        // Allow super-admin when impersonating a tenant
        if ($user->isSuperAdmin() && request()->cookie('impersonating_tenant_id')) {
            return true;
        }

        return ! $user->isSuperAdmin();
    }
}
