<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Industry;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Tenant>
 */
class TenantFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->company();

        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'locale' => 'pt',
            'branding_config' => ['primary_color' => '#1a56db'],
            'notification_config' => [
                'email' => ['enabled' => true, 'recipients' => [fake()->email()]],
                'sms' => ['enabled' => false, 'recipients' => []],
                'missed_call_sms_template' => 'Obrigado por contactar a {company_name}. Responda a algumas perguntas: {intake_url}',
            ],
            'active_services' => ['roofing', 'waterproofing', 'painting', 'insulation', 'facades', 'terraces', 'gutters', 'remodeling'],
        ];
    }

    public function configure(): static
    {
        return $this->afterCreating(function (Tenant $tenant) {
            if ($tenant->industries()->count() === 0) {
                $industry = Industry::first() ?? Industry::factory()->create();
                $tenant->industries()->attach($industry->id);
            }
        });
    }

    /**
     * Explicitly attach a specific industry to the tenant.
     * Use this in tests that need a particular industry, rather than
     * relying on the fallback auto-attach.
     */
    public function withIndustry(Industry $industry): static
    {
        return $this->afterCreating(function (Tenant $tenant) use ($industry) {
            $tenant->industries()->sync([$industry->id]);
        });
    }
}
