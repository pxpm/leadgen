<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\FieldType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeadField extends Model
{
    use HasFactory;

    protected $fillable = [
        'lead_id', 'lead_service_id', 'field_key', 'field_type', 'field_value',
        'field_options', 'confidence', 'is_required',
    ];

    protected function casts(): array
    {
        return [
            'field_type' => FieldType::class,
            'field_options' => 'array',
            'confidence' => 'decimal:2',
            'is_required' => 'boolean',
        ];
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }
}
