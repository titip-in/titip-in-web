<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_get_profile()
    {
        $user = User::factory()->create([
            'wa_number' => '62811111111',
            'status' => 'Mahasiswa FILKOM UB'
        ]);

        $response = $this->actingAs($user)->getJson('/api/v1/me');

        $response->assertStatus(200)
            ->assertJsonPath('data.id', $user->id)
            ->assertJsonPath('data.email', $user->email)
            ->assertJsonPath('data.status', 'Mahasiswa FILKOM UB');
    }

    public function test_unauthenticated_user_cannot_get_profile()
    {
        $response = $this->getJson('/api/v1/me');
        $response->assertStatus(401);
    }

    public function test_authenticated_user_can_update_profile()
    {
        $user = User::factory()->create(['name' => 'Old Name', 'wa_number' => '62811111111', 'status' => 'Bio Lama']);

        $response = $this->actingAs($user)->patchJson('/api/v1/me', [
            'name' => 'New Name',
            'wa_number' => '08222222222',
            'avatar_url' => 'https://example.com/avatar.jpg',
            'status' => 'Asprak BasDat'
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.name', 'New Name')
            ->assertJsonPath('data.wa_number', '628222222222');
    }

    public function test_user_can_update_profile_without_changing_wa_number()
    {
        $user = User::factory()->create(['name' => 'Berd', 'wa_number' => '628999999999']);

        $response = $this->actingAs($user)->patchJson('/api/v1/me', [
            'name' => 'Berd Updated',
            'wa_number' => '08999999999',
        ]);

        $response->assertStatus(200)->assertJsonPath('data.name', 'Berd Updated');
    }

    public function test_user_cannot_use_wa_number_owned_by_another_user()
    {
        User::factory()->create([
            'wa_number' => '628123456789', 
            'wa_verified_at' => now()
        ]);
        
        $user2 = User::factory()->create([
            'wa_number' => '628987654321'
        ]);

        $response = $this->actingAs($user2)->patchJson('/api/v1/me', [
            'wa_number' => '08123456789'
        ]);

        $response->assertStatus(422)
                 ->assertJsonPath('success', false)
                 ->assertJsonPath('message', 'WhatsApp number is already in use by a verified account.');
    }
    
    public function test_unauthenticated_user_cannot_update_profile()
    {
        $response = $this->patchJson('/api/v1/me', ['name' => 'Hacker']);
        $response->assertStatus(401);
    }

    public function test_user_can_soft_delete_own_account()
    {
        $user = User::factory()->create([
            'email' => 'ahmad@example.com',
            'wa_number' => '6281234567890'
        ]);

        $response = $this->actingAs($user)->deleteJson('/api/v1/me');

        $response->assertStatus(200);

        $this->assertSoftDeleted('users', [
            'id' => $user->id,
        ]);

        $deletedUser = User::withTrashed()->find($user->id);
        $this->assertStringContainsString('deleted_', $deletedUser->email);
        $this->assertStringContainsString('deleted_', $deletedUser->wa_number);
    }
}