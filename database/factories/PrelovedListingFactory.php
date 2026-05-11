<?php

namespace Database\Factories;

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
        return [
            'user_id' => UserFactory::new(),
            'category_id' => null,
            'title' => $this->faker->sentence(),
            'description' => $this->faker->paragraph(),
            'price' => $this->faker->numberBetween(100000, 50000000),
            'condition' => $this->faker->randomElement(['NEW', 'LIKE_NEW', 'GOOD', 'FAIR']),
            'image_url' => $this->faker->imageUrl(),
            'status' => $this->faker->randomElement(['AVAILABLE', 'SOLD', 'RESERVED']),
        ];
    }
}
