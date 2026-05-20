<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Category;
use App\Models\PrelovedListing;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PrelovedListing>
 */
class PrelovedListingFactory extends Factory
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
            'title' => $this->faker->sentence(3),
            'description' => $this->faker->paragraph(),
            'price' => $this->faker->numberBetween(100000, 5000000),
            'condition' => $this->faker->randomElement(['NEW', 'LIKE_NEW', 'GOOD', 'FAIR']),
            'status' => $this->faker->randomElement(['AVAILABLE', 'SOLD', 'CLOSED']),
            'embedding' => $dummyVector,
        ];
    }
}