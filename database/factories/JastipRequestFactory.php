<?php

namespace Database\Factories;

use App\Models\JastipRequest;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<JastipRequest>
 */
class JastipRequestFactory extends Factory
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
            'from_loc' => $this->faker->city(),
            'to_loc' => $this->faker->city(),
            'notes' => $this->faker->paragraph(),
            'status' => $this->faker->randomElement(['OPEN', 'TAKEN', 'CLOSED']),
        ];
    }
}
