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
            'slug' => 'construcao_civil',
            'name' => 'Construção Civil',
            'is_active' => true,
        ];
    }

    public function construcaoCivil(): static
    {
        return $this->state(fn () => [
            'slug' => 'construcao_civil',
            'name' => 'Construção Civil',
            'is_active' => true,
        ]);
    }

    public function hvac(): static
    {
        return $this->state(fn () => [
            'slug' => 'hvac',
            'name' => 'Climatização',
            'is_active' => true,
        ]);
    }

    public function electricity(): static
    {
        return $this->state(fn () => [
            'slug' => 'electricity',
            'name' => 'Eletricidade',
            'is_active' => true,
        ]);
    }

    public function plumbing(): static
    {
        return $this->state(fn () => [
            'slug' => 'plumbing',
            'name' => 'Canalização',
            'is_active' => true,
        ]);
    }

    public function landscaping(): static
    {
        return $this->state(fn () => [
            'slug' => 'landscaping',
            'name' => 'Paisagismo',
            'is_active' => true,
        ]);
    }

    public function pestControl(): static
    {
        return $this->state(fn () => [
            'slug' => 'pest_control',
            'name' => 'Controlo de Pragas',
            'is_active' => true,
        ]);
    }
}
