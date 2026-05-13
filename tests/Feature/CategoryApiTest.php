<?php

namespace Tests\Feature;

use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_get_all_categories(): void
    {
        Category::factory()->count(3)->create();

        $response = $this->getJson('/api/v1/categories');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'icon',
                        'type'
                    ]
                ]
            ]);
    }

    public function test_can_get_single_category(): void
    {
        $category = Category::factory()->create();

        $response = $this->getJson("/api/v1/categories/{$category->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'id',
                    'name',
                    'icon',
                    'type'
                ]
            ])
            ->assertJsonPath('data.id', $category->id);
    }

    public function test_get_category_fails_with_invalid_id(): void
    {
        $response = $this->getJson('/api/v1/categories/99999');

        $response->assertStatus(404);
    }
}