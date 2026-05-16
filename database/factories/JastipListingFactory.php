<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Category;
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
        $dummyVector = '[' . implode(',', array_fill(0, 3072, 0.01)) . ']';

        return [
            'user_id' => User::inRandomOrder()->value('id') ?? User::factory(),
            'category_id' => Category::where('type', 'jastip')->inRandomOrder()->value('id'),
            'title' => $this->faker->sentence(3),
            'description' => $this->faker->paragraph(),
            'from_loc' => $this->faker->city(),
            'to_loc' => $this->faker->city(),
            'deadline' => $this->faker->dateTimeBetween('+1 day', '+30 days'),
            'status' => $this->faker->randomElement(['ACTIVE', 'CLOSED']),
            'lat' => $this->faker->latitude(),
            'lng' => $this->faker->longitude(),
            'embedding' => $dummyVector,
        ];
    }
}