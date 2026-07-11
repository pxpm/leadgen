<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Plan;
use App\Models\User;
use App\Services\TenantService;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $this->call([
            IndustrySeeder::class,
            PlanSeeder::class,
        ]);

        $starterPlan = Plan::where('slug', 'starter')->first();

        $tenantService = app(TenantService::class);

        $tenantService->createTenant([
            'name' => 'Telhados Lisboa',
            'slug' => 'telhados-lisboa',
            'locale' => 'pt',
            'industry_id' => 1,
            'admin_name' => 'Telhados Lisboa',
            'admin_email' => 'tenant@telhadoslisboa.pt',
            'admin_password' => 'password',
            'plan_id' => $starterPlan->id,
            'subscription_status' => 'active',
            'notification_config' => [
                'email' => ['enabled' => true, 'recipients' => ['admin@telhadoslisboa.pt']],
                'sms' => ['enabled' => false, 'recipients' => []],
                'missed_call_sms_template' => 'Obrigado por contactar a {company_name}. Responda a algumas perguntas: {intake_url}',
            ],
            'branding_config' => ['primary_color' => '#1a56db'],
            'active_services' => ['roofing', 'waterproofing', 'painting', 'insulation', 'facades', 'terraces', 'gutters', 'remodeling'],
            'send_magic_link' => false,
        ]);

        User::create([
            'tenant_id' => null,
            'name' => 'Super Admin',
            'email' => 'admin@leadgen.com',
            'password' => bcrypt('password'),
            'is_super_admin' => true,
        ]);
    }
}
