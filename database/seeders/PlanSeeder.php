<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Plan;
use Illuminate\Database\Seeder;

class PlanSeeder extends Seeder
{
    public function run(): void
    {
        Plan::create([
            'name' => 'Starter',
            'slug' => 'starter',
            'description' => 'Para pequenos negócios a começar.',
            'limits' => [
                'sms_monthly' => 100,
                'email_monthly' => 500,
                'ai_ingestion_monthly' => 50,
            ],
            'is_public' => true,
            'sort_order' => 1,
            'is_active' => true,
        ]);

        Plan::create([
            'name' => 'Professional',
            'slug' => 'professional',
            'description' => 'Para negócios em crescimento com mais volume.',
            'limits' => [
                'sms_monthly' => 500,
                'email_monthly' => 2000,
                'ai_ingestion_monthly' => 200,
            ],
            'is_public' => true,
            'sort_order' => 2,
            'is_active' => true,
        ]);

        Plan::create([
            'name' => 'Enterprise',
            'slug' => 'enterprise',
            'description' => 'Para operações de alto volume com necessidades customizadas.',
            'limits' => [
                'sms_monthly' => 5000,
                'email_monthly' => 10000,
                'ai_ingestion_monthly' => 1000,
            ],
            'is_public' => true,
            'sort_order' => 3,
            'is_active' => true,
        ]);
    }
}
