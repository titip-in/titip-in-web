<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\JastipListing;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Redis;
use Tests\TestCase;

class AnalyticsApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Redis::flushDB();
    }

    public function test_can_track_click_for_valid_item()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
                         ->postJson('/api/v1/items/jastip_listing/123e4567-e89b-12d3-a456-426614174000/click');

        $response->assertStatus(200)
                 ->assertJsonStructure(['success', 'message']);

        $clicks = Redis::get('clicks:jastip_listing:123e4567-e89b-12d3-a456-426614174000');
        $this->assertEquals(1, $clicks);
    }

    public function test_cannot_track_click_with_invalid_type()
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user)->postJson('/api/v1/items/invalid_type/123/click');
        $response->assertStatus(400);
    }

    public function test_basic_user_cannot_access_analytics_dashboard()
    {
        $user = User::factory()->create(['tier' => 'basic']);

        $response = $this->actingAs($user)->getJson('/api/v1/me/analytics');

        $response->assertStatus(403);
    }

    public function test_plus_user_can_access_basic_analytics()
    {
        $user = User::factory()->create(['tier' => 'plus']);
        $category = Category::factory()->create();
        
        $listing = JastipListing::factory()->create([
            'user_id' => $user->id,
            'category_id' => $category->id
        ]);

        Redis::set("views:jastip_listing:{$listing->id}", 50);
        Redis::set("clicks:jastip_listing:{$listing->id}", 5);

        $response = $this->actingAs($user)->getJson('/api/v1/me/analytics');

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'success',
                     'message',
                     'data' => [
                         'total_views',
                         'total_clicks',
                         'item_details' => [
                             '*' => ['id', 'title', 'type', 'views', 'clicks']
                         ]
                     ]
                 ]);
                 
        $this->assertEquals(50, $response->json('data.total_views'));
        $this->assertEquals(5, $response->json('data.total_clicks'));
        
        $response->assertJsonMissing(['conversion_rate', 'best_item']);
    }

    public function test_pro_user_can_access_full_analytics_with_conversion_rate()
    {
        $user = User::factory()->create(['tier' => 'pro']);
        $category = Category::factory()->create();
        
        $listingBiasa = JastipListing::factory()->create([
            'user_id' => $user->id,
            'category_id' => $category->id,
            'title' => 'Barang Sepi'
        ]);

        $listingRame = JastipListing::factory()->create([
            'user_id' => $user->id,
            'category_id' => $category->id,
            'title' => 'Barang Viral'
        ]);

        Redis::set("views:jastip_listing:{$listingBiasa->id}", 10);
        Redis::set("clicks:jastip_listing:{$listingBiasa->id}", 1);

        Redis::set("views:jastip_listing:{$listingRame->id}", 90);
        Redis::set("clicks:jastip_listing:{$listingRame->id}", 19);

        $response = $this->actingAs($user)->getJson('/api/v1/me/analytics');

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'success',
                     'message',
                     'data' => [
                         'total_views',
                         'total_clicks',
                         'item_details',
                         'conversion_rate',
                         'best_item' => ['id', 'title', 'type', 'views', 'clicks']
                     ]
                 ]);

        $this->assertEquals(100, $response->json('data.total_views'));
        $this->assertEquals(20, $response->json('data.total_clicks'));
        $this->assertEquals(20.0, $response->json('data.conversion_rate'));
        $this->assertEquals($listingRame->id, $response->json('data.best_item.id'));
    }

    public function test_viewing_item_detail_increments_redis_views_stealthily()
    {
        $category = Category::factory()->create();
        $user = User::factory()->create();
        
        $listing = JastipListing::factory()->create([
            'user_id' => $user->id,
            'category_id' => $category->id,
            'status' => 'ACTIVE'
        ]);

        $response = $this->getJson("/api/v1/jastip/listings/{$listing->id}");

        $response->assertStatus(200);

        $views = Redis::get("views:jastip_listing:{$listing->id}");
        $this->assertEquals(1, $views);

        $response->assertJsonMissing(['views' => 1]);
    }

    public function test_analytics_calculates_hybrid_data_correctly()
    {
        $user = User::factory()->create(['tier' => 'pro']);
        $category = Category::factory()->create();
        
        $listing = JastipListing::factory()->create([
            'user_id' => $user->id,
            'category_id' => $category->id,
            'views' => 10,
            'clicks' => 5
        ]);

        Redis::set("views:jastip_listing:{$listing->id}", 5);
        Redis::set("clicks:jastip_listing:{$listing->id}", 2);

        $response = $this->actingAs($user)->getJson('/api/v1/me/analytics');

        $response->assertStatus(200)
                 ->assertJsonPath('data.total_views', 15)
                 ->assertJsonPath('data.total_clicks', 7)
                 ->assertJsonPath('data.item_details.0.views', 15)
                 ->assertJsonPath('data.item_details.0.clicks', 7);
    }

    public function test_sync_command_moves_redis_data_to_database_and_clears_redis()
    {
        $user = User::factory()->create();
        $category = Category::factory()->create();
        
        $listing = JastipListing::factory()->create([
            'user_id' => $user->id,
            'category_id' => $category->id,
            'views' => 100,
            'clicks' => 50
        ]);

        Redis::set("views:jastip_listing:{$listing->id}", 20);
        Redis::set("clicks:jastip_listing:{$listing->id}", 10);

        $this->artisan('analytics:sync')->assertSuccessful();

        $this->assertDatabaseHas('jastip_listings', [
            'id' => $listing->id,
            'views' => 120,
            'clicks' => 60
        ]);

        $this->assertNull(Redis::get("views:jastip_listing:{$listing->id}"));
        $this->assertNull(Redis::get("clicks:jastip_listing:{$listing->id}"));
    }
}