<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\MessageRole;
use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConversationMessage extends Model
{
    use BelongsToTenant, HasFactory;

    protected $fillable = ['tenant_id', 'lead_id', 'role', 'content', 'metadata'];

    protected static function booted(): void
    {
        static::creating(function (ConversationMessage $msg): void {
            if (! $msg->tenant_id && $msg->lead_id) {
                $msg->tenant_id = Lead::withoutGlobalScopes()->find($msg->lead_id)?->tenant_id;
            }
        });
    }

    protected function casts(): array
    {
        return [
            'role' => MessageRole::class,
            'metadata' => 'array',
        ];
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }
}
