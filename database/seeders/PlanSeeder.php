<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Plan;
use Illuminate\Database\Seeder;

class PlanSeeder extends Seeder
{
    public function run(): void
    {
        Plan::updateOrCreate(
            ['slug' => 'trial'],
            [
                'name' => 'Trial',
                'description' => 'Testa a plataforma gratuitamente durante 14 dias.',
                'monthly_price' => 0,
                'yearly_price_per_month' => 0,
                'limits' => [
                    'sms_monthly' => 5,
                    'email_monthly' => 10,
                    'email_ingestion_monthly' => 5,
                    'recovery_call' => false,
                ],
                'is_public' => false,
                'sort_order' => 0,
                'is_active' => true,
                'is_popular' => false,
            ],
        );

        Plan::updateOrCreate(
            ['slug' => 'starter'],
            [
                'name' => 'Starter',
                'description' => 'Para pequenos negócios a começar.',
                'monthly_price' => 39,
                'yearly_price_per_month' => 29,
                'limits' => [
                    'sms_monthly' => 30,
                    'email_monthly' => 30,
                    'email_ingestion_monthly' => 30,
                    'recovery_call' => false,
                ],
                'is_public' => true,
                'sort_order' => 1,
                'is_active' => true,
            ],
        );

        Plan::updateOrCreate(
            ['slug' => 'professional'],
            [
                'name' => 'Professional',
                'description' => 'Para negócios em crescimento com mais volume.',
                'monthly_price' => 65,
                'yearly_price_per_month' => 49,
                'limits' => [
                    'sms_monthly' => 100,
                    'email_monthly' => 150,
                    'email_ingestion_monthly' => 150,
                    'recovery_call' => true,
                ],
                'is_public' => true,
                'sort_order' => 2,
                'is_active' => true,
                'is_popular' => true,
            ],
        );

        Plan::updateOrCreate(
            ['slug' => 'enterprise'],
            [
                'name' => 'Enterprise',
                'description' => 'Para operações de alto volume com necessidades customizadas.',
                'monthly_price' => 129,
                'yearly_price_per_month' => 99,
                'limits' => [
                    'sms_monthly' => 300,
                    'email_monthly' => 1000,
                    'email_ingestion_monthly' => 1000,
                    'recovery_call' => true,
                ],
                'is_public' => true,
                'sort_order' => 3,
                'is_active' => true,
            ],
        );
    }
}
