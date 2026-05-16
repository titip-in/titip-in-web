<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\JastipRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JastipRequestApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_get_all_jastip_requests(): void
    {
        $user = User::factory()->create();
        JastipRequest::factory()->count(3)->create(['user_id' => $user->id, 'status' => 'OPEN']);

        $response = $this->getJson('/api/v1/jastip/requests');

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
                        'from_loc',
                        'to_loc',
                        'status',
                        'user',
                        'category'
                    ]
                ]
            ]);
    }

    public function test_can_get_single_jastip_request(): void
    {
        $user = User::factory()->create();
        $request = JastipRequest::factory()->create(['user_id' => $user->id, 'status' => 'OPEN']);

        $response = $this->getJson("/api/v1/jastip/requests/{$request->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'id',
                    'user_id',
                    'category_id',
                    'title',
                    'description',
                    'from_loc',
                    'to_loc',
                    'user',
                    'category'
                ]
            ])
            ->assertJsonPath('data.id', $request->id);
    }

    public function test_get_request_fails_with_invalid_uuid(): void
    {
        $response = $this->getJson('/api/v1/jastip/requests/not-a-uuid');
        $response->assertStatus(400);
    }

    public function test_get_request_fails_with_nonexistent_id(): void
    {
        $response = $this->getJson('/api/v1/jastip/requests/550e8400-e29b-41d4-a716-446655440000');
        $response->assertStatus(404);
    }

    public function test_authenticated_user_can_create_jastip_request(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->create();

        $response = $this->actingAs($user)
            ->postJson('/api/v1/jastip/requests', [
                'category_id' => $category->id,
                'title' => 'Need iPhone Charger',
                'description' => 'Tolong beliin di iBox',
                'from_loc' => 'Jakarta',
                'to_loc' => 'Bandung',
                'status' => 'OPEN',
            ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'id',
                    'user_id',
                    'title',
                    'description',
                    'from_loc',
                    'to_loc',
                    'user',
                ]
            ]);

        $this->assertDatabaseHas('jastip_requests', [
            'title' => 'Need iPhone Charger',
            'from_loc' => 'Jakarta',
            'to_loc' => 'Bandung',
            'user_id' => $user->id
        ]);
    }

    public function test_create_request_fails_without_authentication(): void
    {
        $category = Category::factory()->create();

        $response = $this->postJson('/api/v1/jastip/requests', [
            'category_id' => $category->id,
            'title' => 'Need iPhone Charger',
            'from_loc' => 'Jakarta',
            'to_loc' => 'Bandung',
        ]);

        $response->assertStatus(401);
    }

    public function test_create_request_fails_with_invalid_status(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->create();

        $response = $this->actingAs($user)
            ->postJson('/api/v1/jastip/requests', [
                'category_id' => $category->id,
                'title' => 'Need iPhone Charger',
                'from_loc' => 'Jakarta',
                'to_loc' => 'Bandung',
                'status' => 'INVALID',
            ]);

        $response->assertStatus(422);
    }

    public function test_create_request_fails_with_missing_required_fields(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->postJson('/api/v1/jastip/requests', [
                'from_loc' => 'Jakarta',
                // missing title and to_loc
            ]);

        $response->assertStatus(422);
    }

    public function test_authenticated_user_can_update_own_request(): void
    {
        $user = User::factory()->create();
        $request = JastipRequest::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)
            ->putJson("/api/v1/jastip/requests/{$request->id}", [
                'title' => 'Updated Title Jastip',
                'from_loc' => 'Surabaya',
                'to_loc' => 'Yogyakarta',
                'status' => 'CLOSED',
            ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data'
            ]);

        $this->assertDatabaseHas('jastip_requests', [
            'id' => $request->id,
            'title' => 'Updated Title Jastip',
            'from_loc' => 'Surabaya',
            'status' => 'CLOSED'
        ]);
    }

    public function test_user_cannot_update_other_users_request(): void
    {
        $owner = User::factory()->create();
        $other_user = User::factory()->create();
        $request = JastipRequest::factory()->create(['user_id' => $owner->id]);

        $response = $this->actingAs($other_user)
            ->putJson("/api/v1/jastip/requests/{$request->id}", [
                'from_loc' => 'Hacked Location',
            ]);

        $response->assertStatus(403);
    }

    public function test_update_request_fails_without_authentication(): void
    {
        $request = JastipRequest::factory()->create();

        $response = $this->putJson("/api/v1/jastip/requests/{$request->id}", [
            'from_loc' => 'Updated Location',
        ]);

        $response->assertStatus(401);
    }

    public function test_update_request_fails_with_invalid_uuid(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->putJson('/api/v1/jastip/requests/not-a-uuid', [
                'from_loc' => 'Updated Location',
            ]);

        $response->assertStatus(400);
    }

    public function test_authenticated_user_can_delete_own_request(): void
    {
        $user = User::factory()->create();
        $request = JastipRequest::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)
            ->deleteJson("/api/v1/jastip/requests/{$request->id}");

        $response->assertStatus(200)
            ->assertJsonStructure(['success', 'message']);

        $this->assertDatabaseMissing('jastip_requests', [
            'id' => $request->id
        ]);
    }

    public function test_user_cannot_delete_other_users_request(): void
    {
        $owner = User::factory()->create();
        $other_user = User::factory()->create();
        $request = JastipRequest::factory()->create(['user_id' => $owner->id]);

        $response = $this->actingAs($other_user)
            ->deleteJson("/api/v1/jastip/requests/{$request->id}");

        $response->assertStatus(403);
    }

    public function test_delete_request_fails_without_authentication(): void
    {
        $request = JastipRequest::factory()->create();

        $response = $this->deleteJson("/api/v1/jastip/requests/{$request->id}");

        $response->assertStatus(401);
    }

    public function test_delete_request_fails_with_invalid_uuid(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->deleteJson('/api/v1/jastip/requests/not-a-uuid');

        $response->assertStatus(400);
    }

    public function test_user_cannot_create_more_than_5_active_requests(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->create();

        JastipRequest::factory()->count(5)->create([
            'user_id' => $user->id,
            'status' => 'OPEN'
        ]);

        $response = $this->actingAs($user)
            ->postJson('/api/v1/jastip/requests', [
                'category_id' => $category->id,
                'title' => 'Request ke 6',
                'from_loc' => 'Jakarta',
                'to_loc' => 'Bandung',
                'status' => 'OPEN',
            ]);

        $response->assertStatus(400);
    }

    public function test_cannot_view_other_users_closed_request(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        
        $closedRequest = JastipRequest::factory()->create([
            'user_id' => $owner->id,
            'status' => 'CLOSED'
        ]);

        $response = $this->actingAs($otherUser)
            ->getJson("/api/v1/jastip/requests/{$closedRequest->id}");

        $response->assertStatus(403);
    }

    public function test_owner_can_view_own_closed_request(): void
    {
        $owner = User::factory()->create();
        
        $closedRequest = JastipRequest::factory()->create([
            'user_id' => $owner->id,
            'status' => 'CLOSED'
        ]);

        $response = $this->actingAs($owner)
            ->getJson("/api/v1/jastip/requests/{$closedRequest->id}");

        $response->assertStatus(200);
    }
}