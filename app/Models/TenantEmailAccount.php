<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Database\Factories\TenantEmailAccountFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class TenantEmailAccount extends Model
{
    /** @use HasFactory<TenantEmailAccountFactory> */
    use BelongsToTenant, HasFactory, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'provider',
        'connection_type',
        'email',
        'name',
        'app_password',
        'access_token',
        'refresh_token',
        'token_metadata',
        'imap_config',
        'smtp_config',
        'status',
        'watch_folder',
        'auto_create_leads',
        'last_synced_at',
        'last_synced_uid',
        'last_error',
    ];

    protected function casts(): array
    {
        return [
            'imap_config' => 'array',
            'smtp_config' => 'array',
            'auto_create_leads' => 'boolean',
            'last_synced_at' => 'datetime',
            'token_metadata' => 'array',
            'access_token' => 'encrypted',
            'refresh_token' => 'encrypted',
            'smtp_config' => 'array',
            'auto_create_leads' => 'boolean',
            'last_synced_at' => 'datetime',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function emailMessages(): HasMany
    {
        return $this->hasMany(LeadEmailMessage::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Default IMAP config for known providers.
     */
    public static function defaultImapConfig(string $provider): array
    {
        return match ($provider) {
            'google' => ['host' => 'imap.gmail.com', 'port' => 993, 'encryption' => 'ssl'],
            'microsoft' => ['host' => 'outlook.office365.com', 'port' => 993, 'encryption' => 'ssl'],
            default => ['host' => '', 'port' => 993, 'encryption' => 'ssl'],
        };
    }

    /**
     * Default SMTP config for known providers.
     */
    public static function defaultSmtpConfig(string $provider): array
    {
        return match ($provider) {
            'google' => ['host' => 'smtp.gmail.com', 'port' => 587, 'encryption' => 'tls'],
            'microsoft' => ['host' => 'smtp.office365.com', 'port' => 587, 'encryption' => 'tls'],
            default => ['host' => '', 'port' => 587, 'encryption' => 'tls'],
        };
    }

    /**
     * Whether this account uses Google OAuth for sending.
     */
    public function isGoogleOAuth(): bool
    {
        return $this->connection_type === 'google_oauth'
            && $this->provider === 'google'
            && $this->access_token !== null;
    }
}
