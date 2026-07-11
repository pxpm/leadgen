<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LeadService extends Model
{
    protected $fillable = ['lead_id', 'service_key', 'status', 'order'];

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function fields(): HasMany
    {
        return $this->hasMany(LeadField::class);
    }
}
