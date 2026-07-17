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
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

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
        'verification_code',
        'verification_code_expires_at',
        'verified_at',
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
            'verification_code_expires_at' => 'datetime',
            'verified_at' => 'datetime',
            'last_synced_at' => 'datetime',
            'token_metadata' => 'array',
            'access_token' => 'encrypted',
            'refresh_token' => 'encrypted',
            'app_password' => 'encrypted',
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

    public function isVerified(): bool
    {
        return $this->verified_at !== null;
    }

    public function isPendingVerification(): bool
    {
        return $this->status === 'pending_verification' && ! $this->isVerified();
    }

    /**
     * Generate a one-time verification token, hash it for storage,
     * and set expiry (15 minutes). Returns the plaintext token for
     * building the signed verification URL.
     */
    public function generateVerificationToken(): string
    {
        $token = Str::random(64);

        $this->update([
            'verification_code' => Hash::make($token),
            'verification_code_expires_at' => now()->addMinutes(15),
        ]);

        return $token;
    }

    /**
     * Build the signed verification URL for this account.
     */
    public function getVerificationUrl(): string
    {
        $token = $this->generateVerificationToken();

        return URL::temporarySignedRoute(
            'email-account.verify',
            now()->addMinutes(15),
            ['account' => $this->id, 'token' => $token]
        );
    }

    /**
     * Verify the account with the given code.
     * Rate-limited: max 5 failed attempts per 15 minutes per account.
     */
    public function verify(string $code): bool
    {
        if ($this->isVerified()) {
            return true;
        }

        // Rate limiting: max 5 failed attempts per 15-minute window
        $attemptKey = 'email_verify_attempts:'.$this->id;
        $attempts = (int) Cache::get($attemptKey, 0);

        if ($attempts >= 5) {
            Log::warning('Email verification rate limit exceeded', [
                'account_id' => $this->id,
                'email' => $this->email,
                'attempts' => $attempts,
            ]);

            return false;
        }

        if ($this->verification_code_expires_at?->isPast()) {
            return false;
        }

        if (! Hash::check($code, (string) $this->verification_code)) {
            // Increment failed attempts; cache key auto-expires after 15 min
            Cache::increment($attemptKey, 1, now()->addMinutes(15));

            Log::info('Email verification: invalid code', [
                'account_id' => $this->id,
                'attempts' => $attempts + 1,
            ]);

            return false;
        }

        // Clear rate-limit counter on success
        Cache::forget($attemptKey);

        $this->update([
            'status' => 'active',
            'verified_at' => now(),
            'verification_code' => null,
            'verification_code_expires_at' => null,
        ]);

        return true;
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

    /**
     * Whether this account uses Microsoft OAuth for sending.
     */
    public function isMicrosoftOAuth(): bool
    {
        return $this->connection_type === 'microsoft_oauth'
            && $this->provider === 'microsoft'
            && $this->access_token !== null;
    }

    /**
     * Whether this account uses any OAuth provider.
     */
    public function isOAuth(): bool
    {
        return $this->isGoogleOAuth() || $this->isMicrosoftOAuth();
    }
}
