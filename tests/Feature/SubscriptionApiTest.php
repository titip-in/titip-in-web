<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;
use App\Services\WhatsAppService;
use Mockery\MockInterface;

class SubscriptionApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Config::set('app.admin_wa_number', '6281234567890');
        putenv('ADMIN_WA_NUMBER=6281234567890');
    }

    public function test_user_can_request_upgrade()
    {
        $user = User::factory()->create(['tier' => 'basic']);

        $this->mock(WhatsAppService::class, function (MockInterface $mock) {
            $mock->shouldReceive('sendMessage')->atLeast()->once()->andReturn(true);
        });

        $response = $this->actingAs($user)->postJson('/api/v1/me/subscriptions/upgrade', [
            'tier' => 'pro',
            'payment_proof_url' => 'https://titipin.com/storage/bukti-bayar.jpg'
        ]);

        $response->assertStatus(200);
    }

    public function test_user_cannot_request_upgrade_without_proof()
    {
        $user = User::factory()->create(['tier' => 'basic']);

        $response = $this->actingAs($user)->postJson('/api/v1/me/subscriptions/upgrade', [
            'tier' => 'pro'
        ]);

        $response->assertStatus(422);
    }

    public function test_user_cannot_request_same_tier()
    {
        $user = User::factory()->create(['tier' => 'plus']);

        $response = $this->actingAs($user)->postJson('/api/v1/me/subscriptions/upgrade', [
            'tier' => 'plus',
            'payment_proof_url' => 'https://titipin.com/storage/bukti-bayar.jpg'
        ]);

        $response->assertStatus(400);
    }
}