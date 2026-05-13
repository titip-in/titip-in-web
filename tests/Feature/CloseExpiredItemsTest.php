<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\JastipListing;
use App\Models\PrelovedListing;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class CloseExpiredItemsTest extends TestCase
{
    use RefreshDatabase;

    public function test_scheduler_closes_expired_items()
    {
        $user = User::factory()->create();

        $expiredJastip = JastipListing::factory()->create([
            'user_id' => $user->id,
            'deadline' => now()->subDays(2),
            'status' => 'ACTIVE'
        ]);

        $activeJastip = JastipListing::factory()->create([
            'user_id' => $user->id,
            'deadline' => now()->addDays(1),
            'status' => 'ACTIVE'
        ]);

        $expiredPreloved = PrelovedListing::factory()->create([
            'user_id' => $user->id,
            'created_at' => now()->subDays(8),
            'status' => 'AVAILABLE'
        ]);

        Artisan::call('titipin:close-expired');

        $this->assertDatabaseHas('jastip_listings', [
            'id' => $expiredJastip->id,
            'status' => 'CLOSED'
        ]);

        $this->assertDatabaseHas('jastip_listings', [
            'id' => $activeJastip->id,
            'status' => 'ACTIVE'
        ]);

        $this->assertDatabaseHas('preloved_listings', [
            'id' => $expiredPreloved->id,
            'status' => 'CLOSED'
        ]);
    }
}
