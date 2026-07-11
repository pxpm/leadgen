<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Industry;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Industry>
 */
class IndustryFactory extends Factory
{
    public function definition(): array
    {
        return [
            'slug' => 'roofing',
            'name' => 'Roofing Contractors',
            'config' => $this->roofingConfig(),
            'is_active' => true,
        ];
    }

    public function roofing(): static
    {
        return $this->state(fn () => [
            'slug' => 'roofing',
            'name' => 'Roofing Contractors',
            'config' => $this->roofingConfig(),
        ]);
    }

    private function roofingConfig(): array
    {
        return require database_path('seeders/data/industries/construcao_civil.php');
    }
}
