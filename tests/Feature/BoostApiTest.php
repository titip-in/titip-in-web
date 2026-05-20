<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\PrelovedListing;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BoostApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_boost_active_item_and_quota_decreases()
    {
        $user = User::factory()->create(['tier' => 'pro', 'boost_quota' => 5]);
        $listing = PrelovedListing::factory()->create([
            'user_id' => $user->id,
            'status' => 'AVAILABLE',
            'boosted_at' => null
        ]);

        $response = $this->actingAs($user)->postJson("/api/v1/preloved/listings/{$listing->id}/boost");

        $response->assertStatus(200)
                 ->assertJsonStructure(['success', 'message', 'data' => ['remaining_quota', 'boosted_at']])
                 ->assertJsonPath('data.remaining_quota', 4);

        $this->assertDatabaseHas('users', ['id' => $user->id, 'boost_quota' => 4]);
        $this->assertDatabaseHas('preloved_listings', ['id' => $listing->id]);
        $this->assertNotNull($listing->fresh()->boosted_at);
    }

    public function test_user_cannot_boost_without_quota()
    {
        $user = User::factory()->create(['tier' => 'basic', 'boost_quota' => 0]);
        $listing = PrelovedListing::factory()->create([
            'user_id' => $user->id,
            'status' => 'AVAILABLE'
        ]);

        $response = $this->actingAs($user)->postJson("/api/v1/preloved/listings/{$listing->id}/boost");

        $response->assertStatus(403);
    }

    public function test_user_cannot_boost_closed_item()
    {
        $user = User::factory()->create(['tier' => 'pro', 'boost_quota' => 5]);
        $listing = PrelovedListing::factory()->create([
            'user_id' => $user->id,
            'status' => 'CLOSED'
        ]);

        $response = $this->actingAs($user)->postJson("/api/v1/preloved/listings/{$listing->id}/boost");

        $response->assertStatus(400);
    }

    public function test_user_cannot_boost_other_users_item()
    {
        $owner = User::factory()->create();
        $hacker = User::factory()->create(['tier' => 'pro', 'boost_quota' => 5]);
        $listing = PrelovedListing::factory()->create([
            'user_id' => $owner->id,
            'status' => 'AVAILABLE'
        ]);

        $response = $this->actingAs($hacker)->postJson("/api/v1/preloved/listings/{$listing->id}/boost");

        $response->assertStatus(403);
    }
}