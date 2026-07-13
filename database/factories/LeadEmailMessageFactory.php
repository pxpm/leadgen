<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Lead;
use App\Models\LeadEmailMessage;
use App\Models\TenantEmailAccount;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LeadEmailMessage>
 */
class LeadEmailMessageFactory extends Factory
{
    public function definition(): array
    {
        return [
            'lead_id' => Lead::factory(),
            'tenant_email_account_id' => TenantEmailAccount::factory(),
            'direction' => fake()->randomElement(['inbound', 'outbound']),
            'message_uid' => fake()->unique()->randomNumber(8),
            'message_id_header' => '<'.fake()->uuid().'@mail.example.com>',
            'subject' => fake()->sentence(),
            'body_text' => fake()->paragraph(),
            'from_address' => fake()->safeEmail(),
            'from_name' => fake()->name(),
            'to_addresses' => [fake()->safeEmail()],
            'received_at' => now(),
        ];
    }

    public function inbound(): static
    {
        return $this->state(fn (array $attributes) => [
            'direction' => 'inbound',
        ]);
    }

    public function outbound(): static
    {
        return $this->state(fn (array $attributes) => [
            'direction' => 'outbound',
        ]);
    }
}
