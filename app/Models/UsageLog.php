<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

class UsageLog extends Model
{
    protected $fillable = ['tenant_id', 'type', 'count', 'period'];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public static function incrementUsage(Tenant $tenant, string $type): void
    {
        $period = now()->format('Y-m');

        self::upsert(
            ['tenant_id' => $tenant->id, 'type' => $type, 'period' => $period, 'count' => 1],
            ['tenant_id', 'type', 'period'],
            ['count' => DB::raw('count + 1')]
        );
    }

    public static function getUsage(Tenant $tenant, string $type): int
    {
        return (int) (self::where('tenant_id', $tenant->id)
            ->where('type', $type)
            ->where('period', now()->format('Y-m'))
            ->value('count') ?? 0);
    }
}
