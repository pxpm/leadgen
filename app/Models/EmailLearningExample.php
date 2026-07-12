<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\FollowUpScenario;
use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmailLearningExample extends Model
{
    use BelongsToTenant, HasFactory;

    protected $fillable = [
        'tenant_id', 'scenario', 'reasons_hash',
        'generated_body', 'sent_body', 'was_edited',
    ];

    protected function casts(): array
    {
        return [
            'was_edited' => 'boolean',
        ];
    }

    /**
     * Record a learning example from a sent follow-up action.
     * Only records if the email was edited (learning opportunity).
     */
    public static function recordFromAction(FollowUpAction $action): void
    {
        if (empty($action->generated_email) || empty($action->final_email)) {
            return;
        }

        $generated = trim($action->generated_email);
        $sent = trim($action->final_email);

        // Only learn from edits (not identical copies)
        $wasEdited = $generated !== $sent;

        $reasons = $action->selected_items ?? [];
        sort($reasons);

        self::create([
            'tenant_id' => $action->tenant_id,
            'scenario' => $action->scenario->value,
            'reasons_hash' => md5(implode(',', $reasons)),
            'generated_body' => $generated,
            'sent_body' => $sent,
            'was_edited' => $wasEdited,
        ]);
    }

    /**
     * Find similar past examples for few-shot learning.
     *
     * @param  array<string>  $reasons
     * @return array<int, array{body: string}>
     */
    public static function findSimilar(int $tenantId, FollowUpScenario $scenario, array $reasons, int $limit = 2): array
    {
        sort($reasons);
        $hash = md5(implode(',', $reasons));

        return self::where('tenant_id', $tenantId)
            ->where('scenario', $scenario->value)
            ->where('reasons_hash', $hash)
            ->where('was_edited', true)  // only learn from contractor's edits
            ->latest()
            ->limit($limit)
            ->get()
            ->map(fn ($example) => ['body' => $example->sent_body])
            ->toArray();
    }
}
