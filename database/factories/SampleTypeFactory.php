<?php

namespace Database\Factories;

use App\Models\SampleType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SampleType>
 */
class SampleTypeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => 'Matrice ' . $this->faker->unique()->word() . ' ' . $this->faker->randomNumber(3),
            'is_active' => true,
        ];
    }
}
