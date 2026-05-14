<?php

namespace Database\Factories;

use App\Models\JastipListing;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<JastipListing>
 */
class JastipListingFactory extends Factory
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
            'deadline' => $this->faker->dateTimeBetween('+1 day', '+30 days'),
            'status' => $this->faker->randomElement(['ACTIVE', 'CLOSED']),
            'lat' => $this->faker->latitude(),
            'lng' => $this->faker->longitude(),
        ];
    }
}