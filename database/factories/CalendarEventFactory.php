<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\CalendarEventCategory;
use App\Enums\CalendarEventStatus;
use App\Models\CalendarEvent;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CalendarEvent>
 */
class CalendarEventFactory extends Factory
{
    protected $model = CalendarEvent::class;

    public function definition(): array
    {
        $start = fake()->dateTimeBetween('now', '+30 days');

        return [
            'tenant_id' => Tenant::factory(),
            'title' => fake()->sentence(3),
            'description' => fake()->optional()->sentence(),
            'category' => fake()->randomElement(CalendarEventCategory::cases()),
            'start_at' => $start,
            'end_at' => (clone $start)->modify('+'.fake()->numberBetween(1, 4).' hours'),
            'all_day' => false,
            'location' => fake()->optional()->address(),
            'status' => CalendarEventStatus::Scheduled,
            'color' => null,
            'is_recurring' => false,
            'created_by' => User::factory(),
        ];
    }

    public function recurring(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_recurring' => true,
            'recurrence_rule' => 'FREQ=WEEKLY;BYDAY=MO,WE,FR',
            'recurrence_ends_at' => now()->addMonths(3),
        ]);
    }

    public function forLead(int $leadId): static
    {
        return $this->state(fn (array $attributes) => [
            'eventable_type' => 'App\\Models\\Lead',
            'eventable_id' => $leadId,
        ]);
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => CalendarEventStatus::Completed,
        ]);
    }

    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => CalendarEventStatus::Cancelled,
        ]);
    }
}
