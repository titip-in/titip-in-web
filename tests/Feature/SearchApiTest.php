<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SearchApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_search_jastip_listings()
    {
        $response = $this->getJson('/api/v1/search?q=jakarta&type=jastip');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'data',
                    'path',
                    'per_page',
                    'next_cursor',
                    'prev_cursor'
                ]
            ]);
    }

    public function test_can_search_preloved_listings()
    {
        $response = $this->getJson('/api/v1/search?q=baju&type=preloved');

        $response->assertStatus(200);
    }

    public function test_search_fails_with_invalid_type()
    {
        $response = $this->getJson('/api/v1/search?q=baju&type=ngawur');

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['type']);
    }
}