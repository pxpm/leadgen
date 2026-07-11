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
            'industry_id' => Industry::first()?->id ?? Industry::factory(),
            'branding_config' => ['primary_color' => '#1a56db'],
            'notification_config' => [
                'email' => ['enabled' => true, 'recipients' => [fake()->email()]],
                'sms' => ['enabled' => false, 'recipients' => []],
                'missed_call_sms_template' => 'Obrigado por contactar a {company_name}. Responda a algumas perguntas: {intake_url}',
            ],
            'active_services' => ['roofing', 'waterproofing', 'painting', 'insulation', 'facades', 'terraces', 'gutters', 'remodeling'],
        ];
    }
}
