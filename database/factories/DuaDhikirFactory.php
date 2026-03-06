<?php

namespace Database\Factories;

use App\Models\DuaDhikir;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DuaDhikir>
 */
class DuaDhikirFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence(3),
            'category' => $this->faker->randomElement(['Morning', 'Evening', 'Prayer', 'General']),
            'arabic_text' => $this->faker->sentence(10),
            'translation' => $this->faker->sentence(15),
            'description' => $this->faker->optional()->sentence(20),
            'image' => null,
            'status' => $this->faker->randomElement(['active', 'inactive']),
        ];
    }
}
