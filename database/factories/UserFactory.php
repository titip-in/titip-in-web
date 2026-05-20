<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->name();
        return [
            'name' => $name,
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
            'wa_number' => '628' . fake()->unique()->numerify('##########'),
            'wa_verified_at' => now(),
            'avatar_url' => 'https://ui-avatars.com/api/?name=' . urlencode($name) . '&background=random',
            'status' => fake()->randomElement(['Mahasiswa FILKOM UB', 'Asprak BasDat', 'Anak Kos Suhat', 'Pejuang Skripsi', 'Tukang Ngoding', null]),
            'tier' => 'basic',
            'boost_quota' => 0,
            'is_banned' => false,
            'tier_expired_at' => null,
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
