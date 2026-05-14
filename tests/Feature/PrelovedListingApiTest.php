<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\PrelovedListing;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PrelovedListingApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_get_all_preloved_listings(): void
    {
        $user = User::factory()->create();
        PrelovedListing::factory()->count(3)->create(['user_id' => $user->id, 'status' => 'AVAILABLE']);

        $response = $this->getJson('/api/v1/preloved/listings');

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
                        'price',
                        'condition',
                        'status',
                        'user',
                        'category',
                        'images'
                    ]
                ]
            ]);
    }

    public function test_can_get_single_preloved_listing(): void
    {
        $user = User::factory()->create();
        $listing = PrelovedListing::factory()->create(['user_id' => $user->id, 'status' => 'AVAILABLE']);

        $response = $this->getJson("/api/v1/preloved/listings/{$listing->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'id',
                    'user_id',
                    'category_id',
                    'title',
                    'price',
                    'condition',
                    'status',
                    'user',
                    'category',
                    'images'
                ]
            ])
            ->assertJsonPath('data.id', $listing->id);
    }

    public function test_get_listing_fails_with_invalid_uuid(): void
    {
        $response = $this->getJson('/api/v1/preloved/listings/not-a-uuid');

        $response->assertStatus(400);
    }

    public function test_get_listing_fails_with_nonexistent_id(): void
    {
        $response = $this->getJson('/api/v1/preloved/listings/550e8400-e29b-41d4-a716-446655440000');

        $response->assertStatus(404);
    }

    public function test_authenticated_user_can_create_preloved_listing(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->create();

        $response = $this->actingAs($user)
            ->postJson('/api/v1/preloved/listings', [
                'category_id' => $category->id,
                'title' => 'Used iPhone 12',
                'description' => 'Good condition',
                'price' => 8000000,
                'condition' => 'GOOD',
                'images' => ['https://example.com/image.jpg'],
                'status' => 'AVAILABLE',
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
                    'price',
                    'condition',
                    'status',
                    'user',
                    'category',
                    'images'
                ]
            ]);

        $this->assertDatabaseHas('preloved_listings', [
            'title' => 'Used iPhone 12',
            'user_id' => $user->id
        ]);
    }

    public function test_create_listing_fails_without_authentication(): void
    {
        $category = Category::factory()->create();

        $response = $this->postJson('/api/v1/preloved/listings', [
            'category_id' => $category->id,
            'title' => 'Used iPhone 12',
            'description' => 'Good condition',
            'price' => 8000000,
            'condition' => 'GOOD',
        ]);

        $response->assertStatus(401);
    }

    public function test_create_listing_fails_with_invalid_condition(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->create();

        $response = $this->actingAs($user)
            ->postJson('/api/v1/preloved/listings', [
                'category_id' => $category->id,
                'title' => 'Used iPhone 12',
                'description' => 'Good condition',
                'price' => 8000000,
                'condition' => 'INVALID',
                'images' => ['https://example.com/image.jpg']
            ]);

        $response->assertStatus(422);
    }

    public function test_create_listing_fails_with_missing_required_fields(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->postJson('/api/v1/preloved/listings', [
                'title' => 'Used iPhone 12',
                // missing price, condition, images
            ]);

        $response->assertStatus(422);
    }

    public function test_authenticated_user_can_update_own_listing(): void
    {
        $user = User::factory()->create();
        $listing = PrelovedListing::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)
            ->putJson("/api/v1/preloved/listings/{$listing->id}", [
                'title' => 'Updated Title',
                'price' => 7000000,
                'condition' => 'LIKE_NEW',
                'status' => 'CLOSED',
            ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data'
            ]);

        $this->assertDatabaseHas('preloved_listings', [
            'id' => $listing->id,
            'title' => 'Updated Title',
            'price' => 7000000,
            'status' => 'CLOSED'
        ]);
    }

    public function test_user_cannot_update_other_users_listing(): void
    {
        $owner = User::factory()->create();
        $other_user = User::factory()->create();
        $listing = PrelovedListing::factory()->create(['user_id' => $owner->id]);

        $response = $this->actingAs($other_user)
            ->putJson("/api/v1/preloved/listings/{$listing->id}", [
                'title' => 'Hacked Title',
            ]);

        $response->assertStatus(403);
    }

    public function test_update_listing_fails_without_authentication(): void
    {
        $listing = PrelovedListing::factory()->create();

        $response = $this->putJson("/api/v1/preloved/listings/{$listing->id}", [
            'title' => 'Updated Title',
        ]);

        $response->assertStatus(401);
    }

    public function test_update_listing_fails_with_invalid_uuid(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->putJson('/api/v1/preloved/listings/not-a-uuid', [
                'title' => 'Updated Title',
            ]);

        $response->assertStatus(400);
    }

    public function test_authenticated_user_can_delete_own_listing(): void
    {
        $user = User::factory()->create();
        $listing = PrelovedListing::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)
            ->deleteJson("/api/v1/preloved/listings/{$listing->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message'
            ]);

        $this->assertDatabaseMissing('preloved_listings', [
            'id' => $listing->id
        ]);
    }

    public function test_user_cannot_delete_other_users_listing(): void
    {
        $owner = User::factory()->create();
        $other_user = User::factory()->create();
        $listing = PrelovedListing::factory()->create(['user_id' => $owner->id]);

        $response = $this->actingAs($other_user)
            ->deleteJson("/api/v1/preloved/listings/{$listing->id}");

        $response->assertStatus(403);
    }

    public function test_delete_listing_fails_without_authentication(): void
    {
        $listing = PrelovedListing::factory()->create();

        $response = $this->deleteJson("/api/v1/preloved/listings/{$listing->id}");

        $response->assertStatus(401);
    }

    public function test_delete_listing_fails_with_invalid_uuid(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->deleteJson('/api/v1/preloved/listings/not-a-uuid');

        $response->assertStatus(400);
    }

    public function test_user_cannot_create_more_than_5_active_listings(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->create();

        PrelovedListing::factory()->count(5)->create([
            'user_id' => $user->id,
            'status' => 'AVAILABLE'
        ]);

        $response = $this->actingAs($user)
            ->postJson('/api/v1/preloved/listings', [
                'category_id' => $category->id,
                'title' => 'Used iPhone 12',
                'price' => 8000000,
                'condition' => 'GOOD',
                'status' => 'AVAILABLE',
                'images' => ['https://example.com/image.jpg']
            ]);

        $response->assertStatus(400);
    }

    public function test_cannot_view_other_users_closed_listing(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        
        $closedListing = PrelovedListing::factory()->create([
            'user_id' => $owner->id,
            'status' => 'CLOSED'
        ]);

        $response = $this->actingAs($otherUser)
            ->getJson("/api/v1/preloved/listings/{$closedListing->id}");

        $response->assertStatus(403);
    }

    public function test_owner_can_view_own_closed_listing(): void
    {
        $owner = User::factory()->create();
        
        $closedListing = PrelovedListing::factory()->create([
            'user_id' => $owner->id,
            'status' => 'CLOSED'
        ]);

        $response = $this->actingAs($owner)
            ->getJson("/api/v1/preloved/listings/{$closedListing->id}");

        $response->assertStatus(200);
    }
}