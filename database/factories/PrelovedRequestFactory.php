<?php

namespace Database\Factories;

use App\Models\PrelovedRequest;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PrelovedRequest>
 */
class PrelovedRequestFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => UserFactory::new(),
            'category_id' => null,
            'title' => $this->faker->sentence(),
            'description' => $this->faker->paragraph(),
            'max_price' => $this->faker->numberBetween(100000, 50000000),
            'status' => $this->faker->randomElement(['OPEN', 'FOUND', 'CLOSED']),
        ];
    }
}
