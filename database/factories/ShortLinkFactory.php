<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Lead;
use App\Models\ShortLink;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<ShortLink>
 */
class ShortLinkFactory extends Factory
{
    public function definition(): array
    {
        return [
            'hash' => Str::random(ShortLink::HASH_LENGTH),
            'tenant_id' => Tenant::factory(),
            'source' => 'direct_link',
            'expires_at' => now()->addHours(24),
        ];
    }

    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'expires_at' => now()->subHour(),
        ]);
    }

    public function withLead(): static
    {
        return $this->state(fn (array $attributes) => [
            'lead_id' => Lead::factory(),
        ]);
    }
}
