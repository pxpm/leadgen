<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\LeadSource;
use App\Enums\LeadStatus;
use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Lead extends Model implements HasMedia
{
    use BelongsToTenant, HasFactory, InteractsWithMedia;

    protected $fillable = [
        'tenant_id', 'industry_id', 'status', 'source',
        'service_type', 'qualification_score', 'notes',
        'session_token', 'pending_services', 'current_field_key',
        'conversation_started_at', 'qualified_at', 'delivered_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => LeadStatus::class,
            'source' => LeadSource::class,
            'qualification_score' => 'integer',
            'pending_services' => 'array',
            'conversation_started_at' => 'datetime',
            'qualified_at' => 'datetime',
            'delivered_at' => 'datetime',
        ];
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('photos')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp']);
        $this->addMediaCollection('documents')
            ->acceptsMimeTypes(['application/pdf']);
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function industry(): BelongsTo
    {
        return $this->belongsTo(Industry::class);
    }

    public function fields(): HasMany
    {
        return $this->hasMany(LeadField::class);
    }

    public function leadServices(): HasMany
    {
        return $this->hasMany(LeadService::class)->orderBy('order');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(ConversationMessage::class);
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }

    public function missedCalls(): HasMany
    {
        return $this->hasMany(MissedCall::class);
    }
}
