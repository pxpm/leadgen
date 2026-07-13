<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Database\Factories\ShortLinkFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class ShortLink extends Model
{
    /** @use HasFactory<ShortLinkFactory> */
    use BelongsToTenant, HasFactory;

    /**
     * Number of characters in the short hash.
     * 6 chars = 56.8B combinations. Links expire in 24h, so collisions are negligible.
     */
    public const HASH_LENGTH = 6;

    protected $fillable = [
        'hash',
        'tenant_id',
        'source',
        'metadata',
        'expires_at',
        'lead_id',
    ];

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
            'expires_at' => 'datetime',
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

    /**
     * Scope to only include active (non-expired) links.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('expires_at', '>', now());
    }

    /**
     * Generate a unique 6-character hash with retry on collision.
     */
    public static function generateHash(): string
    {
        do {
            $hash = Str::random(self::HASH_LENGTH);
        } while (self::where('hash', $hash)->exists());

        return $hash;
    }

    /**
     * Whether this link has expired.
     */
    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    // ─── Named constructors ────────────────────────────────────

    /**
     * Create a short link for direct tenant intake (shareable link).
     */
    public static function forDirectLink(Tenant $tenant, int $ttlHours = 24): self
    {
        return self::create([
            'hash' => self::generateHash(),
            'tenant_id' => $tenant->id,
            'source' => 'direct_link',
            'expires_at' => now()->addHours($ttlHours),
        ]);
    }

    /**
     * Create a short link for missed call intake (sent to the caller).
     */
    public static function forMissedCallIntake(MissedCall $missedCall, int $ttlHours = 48): self
    {
        return self::create([
            'hash' => self::generateHash(),
            'tenant_id' => $missedCall->tenant_id,
            'source' => 'missed_call_intake',
            'metadata' => ['missed_call_id' => $missedCall->id],
            'expires_at' => now()->addHours($ttlHours),
        ]);
    }

    /**
     * Create a short link for missed call send-sms approval (sent to the tenant).
     */
    public static function forMissedCallSendSms(MissedCall $missedCall, int $ttlHours = 24): self
    {
        return self::create([
            'hash' => self::generateHash(),
            'tenant_id' => $missedCall->tenant_id,
            'source' => 'missed_call_send_sms',
            'metadata' => ['missed_call_id' => $missedCall->id],
            'expires_at' => now()->addHours($ttlHours),
        ]);
    }
}
