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

    /** Field value stored when a user declines or skips a field. */
    public const DECLINED = '__declined__';

    /** Internal message sent by the widget's "Skip" chip. */
    public const SKIP_MESSAGE = '__skip__';

    /** current_field_key value when the summary has been shown and we await user confirmation. */
    public const SUMMARY_MARKER = '__summary__';

    /** Token lifetime in hours from creation or last activity. */
    public const TOKEN_TTL_HOURS = 48;

    protected static function booted(): void
    {
        // Auto-sync services JSON → LeadService records so field attribution
        // works consistently across all lead creation paths (widget, manual, email).
        static::saved(function (Lead $lead): void {
            if (! $lead->wasChanged('services')) {
                return;
            }

            $serviceKeys = $lead->services ?? [];
            if (empty($serviceKeys)) {
                return;
            }

            $existingKeys = $lead->leadServices()->pluck('service_key')->toArray();
            $order = $lead->leadServices()->max('order') ?? 0;

            foreach ($serviceKeys as $key) {
                if (in_array($key, $existingKeys, true)) {
                    continue;
                }
                $order++;
                $lead->leadServices()->create([
                    'service_key' => $key,
                    'status' => 'in_progress',
                    'order' => $order,
                ]);
            }
        });
    }

    protected $fillable = [
        'tenant_id', 'industry_id', 'status', 'source',
        'services', 'qualification_score', 'notes',
        'session_token', 'token_expires_at', 'current_field_key',
        'conversation_started_at', 'qualified_at', 'delivered_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => LeadStatus::class,
            'source' => LeadSource::class,
            'qualification_score' => 'integer',
            'services' => 'array',
            'conversation_started_at' => 'datetime',
            'token_expires_at' => 'datetime',
            'qualified_at' => 'datetime',
            'delivered_at' => 'datetime',
        ];
    }

    /**
     * Get all services as a flat array.
     *
     * @return array<int, string>
     */
    public function getAllServices(): array
    {
        return $this->services ?? [];
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('photos')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp']);
        $this->addMediaCollection('documents')
            ->acceptsMimeTypes([
                'application/pdf',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ]);
    }

    /**
     * Whether the session token is still valid (not expired).
     */
    public function isTokenExpired(): bool
    {
        return $this->token_expires_at !== null && $this->token_expires_at->isPast();
    }

    /**
     * Extend the token expiry by TOKEN_TTL_HOURS from now.
     */
    public function extendToken(): void
    {
        $this->update(['token_expires_at' => now()->addHours(self::TOKEN_TTL_HOURS)]);
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

    public function emailMessages(): HasMany
    {
        return $this->hasMany(LeadEmailMessage::class)->orderBy('received_at', 'asc');
    }
}
