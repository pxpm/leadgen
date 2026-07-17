<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LeadService extends Model
{
    use BelongsToTenant;

    protected $fillable = ['tenant_id', 'lead_id', 'service_key', 'status', 'order'];

    protected static function booted(): void
    {
        static::creating(function (LeadService $service): void {
            if (! $service->tenant_id && $service->lead_id) {
                $service->tenant_id = Lead::withoutGlobalScopes()->find($service->lead_id)?->tenant_id;
            }
        });
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function fields(): HasMany
    {
        return $this->hasMany(LeadField::class);
    }
}
