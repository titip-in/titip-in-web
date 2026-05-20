<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_register(): void
    {
        $response = $this->postJson('/api/v1/register', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
            'wa_number' => '081234567890', 
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'access_token',
                    'token_type',
                    'user' => ['id', 'name', 'email', 'wa_number']
                ]
            ]);

        $this->assertDatabaseHas('users', [
            'email' => 'john@example.com',
            'wa_number' => '6281234567890' 
        ]);
    }

    public function test_register_fails_with_duplicate_email(): void
    {
        User::factory()->create([
            'email' => 'john@example.com',
            'email_verified_at' => now()
        ]);

        $response = $this->postJson('/api/v1/register', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
            'wa_number' => '081234567891',
        ]);

        $response->assertStatus(422);
    }

    public function test_register_fails_with_duplicate_wa_number(): void
    {
        User::factory()->create([
            'email' => 'existing@example.com',
            'wa_number' => '6281234567890',
            'wa_verified_at' => now()
        ]);

        $response = $this->postJson('/api/v1/register', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
            'wa_number' => '081234567890',
        ]);

        $response->assertStatus(422);
    }

    public function test_register_fails_with_invalid_data(): void
    {
        $response = $this->postJson('/api/v1/register', [
            'name' => 'John Doe',
            'password' => 'password123',
            'wa_number' => '081234567890',
        ]);

        $response->assertStatus(422);
    }

    public function test_register_fails_with_short_password(): void
    {
        $response = $this->postJson('/api/v1/register', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'pass123',
            'wa_number' => '081234567890',
        ]);

        $response->assertStatus(422);
    }

    public function test_user_can_login(): void
    {
        User::factory()->create([
            'email' => 'john@example.com',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->postJson('/api/v1/login', [
            'email' => 'john@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(200)->assertJsonStructure(['success', 'message', 'data' => ['access_token']]);
    }

    public function test_login_fails_with_invalid_email(): void
    {
        User::factory()->create(['email' => 'john@example.com', 'password' => bcrypt('password123')]);

        $response = $this->postJson('/api/v1/login', ['email' => 'wrong@example.com', 'password' => 'password123']);
        $response->assertStatus(401);
    }

    public function test_login_fails_with_invalid_password(): void
    {
        User::factory()->create(['email' => 'john@example.com', 'password' => bcrypt('password123')]);

        $response = $this->postJson('/api/v1/login', ['email' => 'john@example.com', 'password' => 'wrongpassword']);
        $response->assertStatus(401);
    }

    public function test_user_can_logout(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('auth_token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")->postJson('/api/v1/logout');
        $response->assertStatus(200);
    }

    public function test_logout_fails_without_authentication(): void
    {
        $response = $this->postJson('/api/v1/logout');

        $response->assertStatus(401);
    }
}