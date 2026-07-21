<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Plan;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Plan>
 */
class PlanFactory extends Factory
{
    protected $model = Plan::class;

    public function definition(): array
    {
        $name = fake()->unique()->word();

        return [
            'name' => ucfirst($name),
            'slug' => $name,
            'monthly_price' => fake()->numberBetween(10, 200),
            'yearly_price_per_month' => fake()->numberBetween(5, 180),
            'description' => fake()->sentence(),
            'limits' => [
                'sms_monthly' => fake()->numberBetween(50, 500),
                'email_monthly' => fake()->numberBetween(200, 2000),
                'email_ingestion_monthly' => fake()->numberBetween(20, 200),
            ],
            'is_public' => true,
            'sort_order' => 0,
            'is_active' => true,
        ];
    }

    public function public(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_public' => true,
        ]);
    }

    public function private(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_public' => false,
        ]);
    }

    public function popular(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_popular' => true,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
