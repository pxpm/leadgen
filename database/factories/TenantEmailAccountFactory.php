<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Tenant;
use App\Models\TenantEmailAccount;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TenantEmailAccount>
 */
class TenantEmailAccountFactory extends Factory
{
    public function definition(): array
    {
        $provider = fake()->randomElement(['google', 'microsoft', 'custom']);

        return [
            'tenant_id' => Tenant::factory(),
            'provider' => $provider,
            'email' => fake()->unique()->safeEmail(),
            'name' => fake()->name(),
            'app_password' => 'encrypted_test_password',
            'imap_config' => TenantEmailAccount::defaultImapConfig($provider),
            'smtp_config' => TenantEmailAccount::defaultSmtpConfig($provider),
            'status' => 'active',
            'auto_create_leads' => false,
        ];
    }

    public function withWatchFolder(string $folder = 'Leads'): static
    {
        return $this->state(fn (array $attributes) => [
            'watch_folder' => $folder,
            'auto_create_leads' => true,
        ]);
    }

    public function errored(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'error',
            'last_error' => 'Connection refused',
        ]);
    }

    public function disconnected(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'disconnected',
        ]);
    }
}
