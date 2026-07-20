<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Industry;
use Illuminate\Database\Seeder;

class IndustrySeeder extends Seeder
{
    public function run(): void
    {
        $industries = [
            'construcao_civil' => 'Construção Civil',
            'hvac' => 'Climatização',
            'electricity' => 'Eletricidade',
            'plumbing' => 'Canalização',
            'landscaping' => 'Paisagismo',
            'pest_control' => 'Controlo de Pragas',
        ];

        foreach ($industries as $slug => $name) {
            Industry::updateOrCreate(
                ['slug' => $slug],
                [
                    'name' => $name,
                    'is_active' => true,
                ]
            );
        }
    }
}
