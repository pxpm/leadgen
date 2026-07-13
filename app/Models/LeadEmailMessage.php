<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\LeadEmailMessageFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeadEmailMessage extends Model
{
    /** @use HasFactory<LeadEmailMessageFactory> */
    use HasFactory;

    protected $fillable = [
        'lead_id',
        'tenant_email_account_id',
        'direction',
        'message_uid',
        'message_id_header',
        'in_reply_to_header',
        'references_header',
        'subject',
        'body_text',
        'body_html',
        'from_address',
        'from_name',
        'to_addresses',
        'cc_addresses',
        'attachment_media_ids',
        'raw_headers',
        'ai_extracted_fields',
        'received_at',
    ];

    protected function casts(): array
    {
        return [
            'to_addresses' => 'array',
            'cc_addresses' => 'array',
            'attachment_media_ids' => 'array',
            'raw_headers' => 'array',
            'ai_extracted_fields' => 'array',
            'received_at' => 'datetime',
        ];
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function tenantEmailAccount(): BelongsTo
    {
        return $this->belongsTo(TenantEmailAccount::class);
    }

    public function isInbound(): bool
    {
        return $this->direction === 'inbound';
    }

    public function isOutbound(): bool
    {
        return $this->direction === 'outbound';
    }
}
