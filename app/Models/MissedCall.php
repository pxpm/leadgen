<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MissedCall extends Model
{
    use BelongsToTenant, HasFactory;

    protected $fillable = [
        'tenant_id', 'caller_number', 'tenant_phone',
        'twilio_call_sid', 'matched_by', 'sms_sent', 'lead_id',
        'intent', 'tenant_notified_at', 'caller_sms_sent_at',
    ];

    protected function casts(): array
    {
        return [
            'sms_sent' => 'boolean',
            'tenant_notified_at' => 'datetime',
            'caller_sms_sent_at' => 'datetime',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }
}
