<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Tenant extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;

    protected $fillable = [
        'name', 'slug', 'locale', 'industry_id',
        'stripe_customer_id',
        'twilio_phone_number', 'twilio_phone_sid',
        'branding_config', 'notification_config',
        'active_services', 'service_config', 'qualification_overrides',
    ];

    protected function casts(): array
    {
        return [
            'branding_config' => 'array',
            'notification_config' => 'array',
            'active_services' => 'array',
            'service_config' => 'array',
            'qualification_overrides' => 'array',
        ];
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('logo')
            ->singleFile()
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp']);
    }

    public function industry(): BelongsTo
    {
        return $this->belongsTo(Industry::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function leads(): HasMany
    {
        return $this->hasMany(Lead::class);
    }

    public function phoneNumbers(): HasMany
    {
        return $this->hasMany(TenantPhoneNumber::class);
    }

    public function excludedNumbers(): HasMany
    {
        return $this->hasMany(TenantExcludedNumber::class);
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function activeSubscription(): HasOne
    {
        return $this->hasOne(Subscription::class)->whereIn('status', ['active', 'trialing']);
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }

    public function missedCalls(): HasMany
    {
        return $this->hasMany(MissedCall::class);
    }

    public function emailAccounts(): HasMany
    {
        return $this->hasMany(TenantEmailAccount::class);
    }

    /**
     * Get the current plan from the active subscription.
     */
    public function getPlanAttribute(): ?Plan
    {
        return $this->activeSubscription?->plan;
    }

    /**
     * Whether the tenant has an active or trialing subscription.
     */
    public function isActive(): bool
    {
        return $this->subscriptions()
            ->whereIn('status', ['active', 'trialing'])
            ->exists();
    }
}
