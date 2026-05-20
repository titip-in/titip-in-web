<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\User;
use App\Models\JastipListing;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Admin::create([
            'name' => 'Super Admin',
            'email' => 'admin@titipin.me',
            'password' => bcrypt('password_admin'),
        ]);
    }

    public function test_admin_can_login()
    {
        $response = $this->postJson('/api/v1/admin/login', [
            'email' => 'admin@titipin.me',
            'password' => 'password_admin',
        ]);

        $response->assertStatus(200)
                 ->assertJsonStructure(['success', 'data' => ['access_token']]);
    }

    public function test_admin_can_get_users_list()
    {
        $admin = Admin::first();
        $token = $admin->createToken('admin_token')->plainTextToken;
        User::factory()->count(5)->create();

        $response = $this->withHeader('Authorization', "Bearer $token")
                         ->getJson('/api/v1/admin/users');

        $response->assertStatus(200)
                 ->assertJsonStructure(['success', 'data' => ['data']]);
    }

    public function test_admin_can_update_user_tier()
    {
        $admin = Admin::first();
        $token = $admin->createToken('admin_token')->plainTextToken;
        $user = User::factory()->create(['tier' => 'basic']);

        $response = $this->withHeader('Authorization', "Bearer $token")
                         ->patchJson("/api/v1/admin/users/{$user->id}/tier", [
                             'tier' => 'pro'
                         ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'tier' => 'pro',
            'boost_quota' => 5
        ]);
    }

    public function test_admin_can_ban_user()
    {
        $admin = Admin::first();
        $token = $admin->createToken('admin_token')->plainTextToken;
        $user = User::factory()->create(['is_banned' => false]);

        $response = $this->withHeader('Authorization', "Bearer $token")
                         ->postJson("/api/v1/admin/users/{$user->id}/ban");

        $response->assertStatus(200);
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'is_banned' => true
        ]);
    }

    public function test_banned_user_cannot_login()
    {
        $user = User::factory()->create([
            'email' => 'banned@example.com',
            'password' => bcrypt('password123'),
            'is_banned' => true
        ]);

        $response = $this->postJson('/api/v1/login', [
            'email' => 'banned@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(403);
    }

    public function test_admin_can_force_delete_item()
    {
        $admin = Admin::first();
        $token = $admin->createToken('admin_token')->plainTextToken;
        $listing = JastipListing::factory()->create();

        $response = $this->withHeader('Authorization', "Bearer $token")
                         ->deleteJson("/api/v1/admin/items/jastip_listing/{$listing->id}");

        $response->assertStatus(200);
        $this->assertDatabaseMissing('jastip_listings', [
            'id' => $listing->id
        ]);
    }

    public function test_admin_can_get_all_items_by_type()
    {
        $admin = Admin::first();
        $token = $admin->createToken('admin_token')->plainTextToken;
        
        JastipListing::factory()->count(3)->create();

        $response = $this->withHeader('Authorization', "Bearer $token")
                         ->getJson('/api/v1/admin/items/jastip_listing');

        $response->assertStatus(200)
                 ->assertJsonStructure(['success', 'message', 'data']);
        
        $this->assertCount(3, $response->json('data'));
    }

    public function test_admin_can_filter_items_by_user_id()
    {
        $admin = Admin::first();
        $token = $admin->createToken('admin_token')->plainTextToken;
        
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        JastipListing::factory()->count(2)->create(['user_id' => $userA->id]);
        JastipListing::factory()->count(1)->create(['user_id' => $userB->id]);

        $response = $this->withHeader('Authorization', "Bearer $token")
                         ->getJson("/api/v1/admin/items/jastip_listing?user_id={$userA->id}");

        $response->assertStatus(200);
        $this->assertCount(2, $response->json('data'));
    }

    public function test_admin_can_get_item_detail()
    {
        $admin = Admin::first();
        $token = $admin->createToken('admin_token')->plainTextToken;
        
        $user = User::factory()->create();
        $category = \App\Models\Category::factory()->create();
        
        $listing = JastipListing::factory()->create([
            'user_id' => $user->id,
            'category_id' => $category->id
        ]);

        $response = $this->withHeader('Authorization', "Bearer $token")
                         ->getJson("/api/v1/admin/items/jastip_listing/{$listing->id}");

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'success',
                     'message',
                     'data' => [
                         'id',
                         'user_id',
                         'title',
                         'user' => ['id', 'name', 'email', 'wa_number', 'is_banned', 'avatar_url'],
                         'category' => ['id', 'name'],
                         'images'
                     ]
                 ])
                 ->assertJsonPath('data.id', $listing->id);
    }

    public function test_admin_get_item_detail_fails_with_invalid_type()
    {
        $admin = Admin::first();
        $token = $admin->createToken('admin_token')->plainTextToken;
        
        $listing = JastipListing::factory()->create();

        $response = $this->withHeader('Authorization', "Bearer $token")
                         ->getJson("/api/v1/admin/items/tipe_ngasal/{$listing->id}");

        $response->assertStatus(400);
    }

    public function test_admin_get_item_detail_fails_with_not_found()
    {
        $admin = Admin::first();
        $token = $admin->createToken('admin_token')->plainTextToken;
        
        $response = $this->withHeader('Authorization', "Bearer $token")
                         ->getJson("/api/v1/admin/items/jastip_listing/550e8400-e29b-41d4-a716-446655440000");

        $response->assertStatus(404);
    }

    public function test_admin_can_force_delete_user()
    {
        $admin = Admin::first();
        $token = $admin->createToken('admin_token')->plainTextToken;
        
        $user = User::factory()->create();

        $response = $this->withHeader('Authorization', "Bearer $token")
                         ->deleteJson("/api/v1/admin/users/{$user->id}");

        $response->assertStatus(200);

        $this->assertDatabaseMissing('users', [
            'id' => $user->id,
        ]);
    }
}