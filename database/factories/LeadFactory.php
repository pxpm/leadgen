<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\LeadSource;
use App\Enums\LeadStatus;
use App\Models\Lead;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Lead>
 */
class LeadFactory extends Factory
{
    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'status' => LeadStatus::New,
            'source' => fake()->randomElement(LeadSource::cases()),
            'session_token' => Str::random(64),
        ];
    }

    public function inProgress(): static
    {
        return $this->state(fn () => [
            'status' => LeadStatus::InProgress,
            'conversation_started_at' => now(),
        ]);
    }

    public function qualified(): static
    {
        return $this->state(fn () => [
            'status' => LeadStatus::Qualified,
            'conversation_started_at' => now()->subMinutes(3),
            'qualified_at' => now(),
            'qualification_score' => fake()->numberBetween(1, 10),
        ]);
    }
}
