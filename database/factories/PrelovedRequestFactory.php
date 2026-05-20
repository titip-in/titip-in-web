<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Category;
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
        $dummyVector = '[' . implode(',', array_fill(0, 3072, 0.01)) . ']';

        return [
            'user_id' => User::inRandomOrder()->value('id') ?? User::factory(),
            'category_id' => Category::where('type', 'preloved')->inRandomOrder()->value('id'),
            'title' => $this->faker->sentence(),
            'description' => $this->faker->paragraph(),
            'max_price' => $this->faker->numberBetween(100000, 50000000),
            'status' => $this->faker->randomElement(['OPEN', 'FOUND', 'CLOSED']),
            'embedding' => $dummyVector,
        ];
    }
}
