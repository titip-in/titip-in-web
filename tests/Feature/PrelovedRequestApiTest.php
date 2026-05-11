<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\PrelovedRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PrelovedRequestApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_get_all_preloved_requests(): void
    {
        $user = User::factory()->create();
        PrelovedRequest::factory()->count(3)->create(['user_id' => $user->id]);

        $response = $this->getJson('/api/v1/preloved/requests');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    '*' => [
                        'id',
                        'user_id',
                        'category_id',
                        'title',
                        'description',
                        'max_price',
                        'status',
                        'user',
                        'category'
                    ]
                ]
            ]);
    }

    public function test_can_get_single_preloved_request(): void
    {
        $user = User::factory()->create();
        $request = PrelovedRequest::factory()->create(['user_id' => $user->id]);

        $response = $this->getJson("/api/v1/preloved/requests/{$request->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'id',
                    'user_id',
                    'category_id',
                    'title',
                    'user',
                    'category'
                ]
            ])
            ->assertJsonPath('data.id', $request->id);
    }

    public function test_get_request_fails_with_invalid_uuid(): void
    {
        $response = $this->getJson('/api/v1/preloved/requests/not-a-uuid');

        $response->assertStatus(400);
    }

    public function test_get_request_fails_with_nonexistent_id(): void
    {
        $response = $this->getJson('/api/v1/preloved/requests/550e8400-e29b-41d4-a716-446655440000');

        $response->assertStatus(404);
    }

    public function test_authenticated_user_can_create_preloved_request(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->create();

        $response = $this->actingAs($user)
            ->postJson('/api/v1/preloved/requests', [
                'category_id' => $category->id,
                'title' => 'Looking for iPhone 12',
                'description' => 'Preferably in black',
                'max_price' => 9000000,
                'status' => 'OPEN',
            ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'id',
                    'user_id',
                    'category_id',
                    'title',
                    'user',
                    'category'
                ]
            ]);

        $this->assertDatabaseHas('preloved_requests', [
            'title' => 'Looking for iPhone 12',
            'user_id' => $user->id
        ]);
    }

    public function test_create_request_fails_without_authentication(): void
    {
        $category = Category::factory()->create();

        $response = $this->postJson('/api/v1/preloved/requests', [
            'category_id' => $category->id,
            'title' => 'Looking for iPhone 12',
            'description' => 'Preferably in black',
            'max_price' => 9000000,
        ]);

        $response->assertStatus(401);
    }

    public function test_create_request_fails_with_invalid_status(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->create();

        $response = $this->actingAs($user)
            ->postJson('/api/v1/preloved/requests', [
                'category_id' => $category->id,
                'title' => 'Looking for iPhone 12',
                'description' => 'Preferably in black',
                'max_price' => 9000000,
                'status' => 'INVALID',
            ]);

        $response->assertStatus(422);
    }

    public function test_create_request_fails_with_missing_required_fields(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->postJson('/api/v1/preloved/requests', [
                // missing title
            ]);

        $response->assertStatus(422);
    }

    public function test_authenticated_user_can_update_own_request(): void
    {
        $user = User::factory()->create();
        $request = PrelovedRequest::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)
            ->putJson("/api/v1/preloved/requests/{$request->id}", [
                'title' => 'Updated Request Title',
                'max_price' => 8000000,
                'status' => 'FOUND',
            ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data'
            ]);

        $this->assertDatabaseHas('preloved_requests', [
            'id' => $request->id,
            'title' => 'Updated Request Title'
        ]);
    }

    public function test_user_cannot_update_other_users_request(): void
    {
        $owner = User::factory()->create();
        $other_user = User::factory()->create();
        $request = PrelovedRequest::factory()->create(['user_id' => $owner->id]);

        $response = $this->actingAs($other_user)
            ->putJson("/api/v1/preloved/requests/{$request->id}", [
                'title' => 'Hacked Title',
            ]);

        $response->assertStatus(403);
    }

    public function test_update_request_fails_without_authentication(): void
    {
        $request = PrelovedRequest::factory()->create();

        $response = $this->putJson("/api/v1/preloved/requests/{$request->id}", [
            'title' => 'Updated Title',
        ]);

        $response->assertStatus(401);
    }

    public function test_update_request_fails_with_invalid_uuid(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->putJson('/api/v1/preloved/requests/not-a-uuid', [
                'title' => 'Updated Title',
            ]);

        $response->assertStatus(400);
    }

    public function test_authenticated_user_can_delete_own_request(): void
    {
        $user = User::factory()->create();
        $request = PrelovedRequest::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)
            ->deleteJson("/api/v1/preloved/requests/{$request->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message'
            ]);

        $this->assertDatabaseMissing('preloved_requests', [
            'id' => $request->id
        ]);
    }

    public function test_user_cannot_delete_other_users_request(): void
    {
        $owner = User::factory()->create();
        $other_user = User::factory()->create();
        $request = PrelovedRequest::factory()->create(['user_id' => $owner->id]);

        $response = $this->actingAs($other_user)
            ->deleteJson("/api/v1/preloved/requests/{$request->id}");

        $response->assertStatus(403);
    }

    public function test_delete_request_fails_without_authentication(): void
    {
        $request = PrelovedRequest::factory()->create();

        $response = $this->deleteJson("/api/v1/preloved/requests/{$request->id}");

        $response->assertStatus(401);
    }

    public function test_delete_request_fails_with_invalid_uuid(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->deleteJson('/api/v1/preloved/requests/not-a-uuid');

        $response->assertStatus(400);
    }
}
