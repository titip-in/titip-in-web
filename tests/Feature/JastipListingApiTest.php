<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\JastipListing;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JastipListingApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_get_all_jastip_listings(): void
    {
        $user = User::factory()->create();
        JastipListing::factory()->count(3)->create(['user_id' => $user->id]);

        $response = $this->getJson('/api/v1/jastip/listings');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    '*' => [
                        'id',
                        'user_id',
                        'category_id',
                        'from_loc',
                        'to_loc',
                        'deadline',
                        'status',
                        'image_url',
                        'lat',
                        'lng',
                        'user',
                        'category'
                    ]
                ]
            ]);
    }

    public function test_can_get_single_jastip_listing(): void
    {
        $user = User::factory()->create();
        $listing = JastipListing::factory()->create(['user_id' => $user->id]);

        $response = $this->getJson("/api/v1/jastip/listings/{$listing->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'id',
                    'user_id',
                    'category_id',
                    'from_loc',
                    'to_loc',
                    'deadline',
                    'user',
                    'category'
                ]
            ])
            ->assertJsonPath('data.id', $listing->id);
    }

    public function test_get_listing_fails_with_invalid_uuid(): void
    {
        $response = $this->getJson('/api/v1/jastip/listings/not-a-uuid');

        $response->assertStatus(400);
    }

    public function test_get_listing_fails_with_nonexistent_id(): void
    {
        $response = $this->getJson('/api/v1/jastip/listings/550e8400-e29b-41d4-a716-446655440000');

        $response->assertStatus(404);
    }

    public function test_authenticated_user_can_create_jastip_listing(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->create();

        $response = $this->actingAs($user)
            ->postJson('/api/v1/jastip/listings', [
                'category_id' => $category->id,
                'from_loc' => 'Jakarta',
                'to_loc' => 'Bandung',
                'deadline' => '2026-06-01',
                'status' => 'ACTIVE',
                'image_url' => 'https://example.com/image.jpg',
                'lat' => -6.1753,
                'lng' => 106.8249,
            ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'id',
                    'user_id',
                    'from_loc',
                    'to_loc',
                    'deadline',
                    'user',
                ]
            ]);

        $this->assertDatabaseHas('jastip_listings', [
            'from_loc' => 'Jakarta',
            'to_loc' => 'Bandung',
            'user_id' => $user->id
        ]);
    }

    public function test_create_listing_fails_without_authentication(): void
    {
        $category = Category::factory()->create();

        $response = $this->postJson('/api/v1/jastip/listings', [
            'category_id' => $category->id,
            'from_loc' => 'Jakarta',
            'to_loc' => 'Bandung',
            'deadline' => '2026-06-01',
        ]);

        $response->assertStatus(401);
    }

    public function test_create_listing_fails_with_invalid_status(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->create();

        $response = $this->actingAs($user)
            ->postJson('/api/v1/jastip/listings', [
                'category_id' => $category->id,
                'from_loc' => 'Jakarta',
                'to_loc' => 'Bandung',
                'deadline' => '2026-06-01',
                'status' => 'INVALID',
            ]);

        $response->assertStatus(422);
    }

    public function test_create_listing_fails_with_invalid_deadline(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->create();

        $response = $this->actingAs($user)
            ->postJson('/api/v1/jastip/listings', [
                'category_id' => $category->id,
                'from_loc' => 'Jakarta',
                'to_loc' => 'Bandung',
                'deadline' => 'not-a-date',
            ]);

        $response->assertStatus(422);
    }

    public function test_create_listing_fails_with_missing_required_fields(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->postJson('/api/v1/jastip/listings', [
                'from_loc' => 'Jakarta',
                // missing to_loc and deadline
            ]);

        $response->assertStatus(422);
    }

    public function test_authenticated_user_can_update_own_listing(): void
    {
        $user = User::factory()->create();
        $listing = JastipListing::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)
            ->putJson("/api/v1/jastip/listings/{$listing->id}", [
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

        $this->assertDatabaseHas('jastip_listings', [
            'id' => $listing->id,
            'from_loc' => 'Surabaya'
        ]);
    }

    public function test_user_cannot_update_other_users_listing(): void
    {
        $owner = User::factory()->create();
        $other_user = User::factory()->create();
        $listing = JastipListing::factory()->create(['user_id' => $owner->id]);

        $response = $this->actingAs($other_user)
            ->putJson("/api/v1/jastip/listings/{$listing->id}", [
                'from_loc' => 'Hacked Location',
            ]);

        $response->assertStatus(403);
    }

    public function test_update_listing_fails_without_authentication(): void
    {
        $listing = JastipListing::factory()->create();

        $response = $this->putJson("/api/v1/jastip/listings/{$listing->id}", [
            'from_loc' => 'Updated Location',
        ]);

        $response->assertStatus(401);
    }

    public function test_update_listing_fails_with_invalid_uuid(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->putJson('/api/v1/jastip/listings/not-a-uuid', [
                'from_loc' => 'Updated Location',
            ]);

        $response->assertStatus(400);
    }

    public function test_authenticated_user_can_delete_own_listing(): void
    {
        $user = User::factory()->create();
        $listing = JastipListing::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)
            ->deleteJson("/api/v1/jastip/listings/{$listing->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message'
            ]);

        $this->assertDatabaseMissing('jastip_listings', [
            'id' => $listing->id
        ]);
    }

    public function test_user_cannot_delete_other_users_listing(): void
    {
        $owner = User::factory()->create();
        $other_user = User::factory()->create();
        $listing = JastipListing::factory()->create(['user_id' => $owner->id]);

        $response = $this->actingAs($other_user)
            ->deleteJson("/api/v1/jastip/listings/{$listing->id}");

        $response->assertStatus(403);
    }

    public function test_delete_listing_fails_without_authentication(): void
    {
        $listing = JastipListing::factory()->create();

        $response = $this->deleteJson("/api/v1/jastip/listings/{$listing->id}");

        $response->assertStatus(401);
    }

    public function test_delete_listing_fails_with_invalid_uuid(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->deleteJson('/api/v1/jastip/listings/not-a-uuid');

        $response->assertStatus(400);
    }
}
