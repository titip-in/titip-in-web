<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\User;
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

    public function test_authenticated_user_can_create_category(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->postJson('/api/v1/categories', [
                'name' => 'Electronics',
                'icon' => '📱',
                'type' => 'jastip',
            ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'id',
                    'name',
                    'icon',
                    'type'
                ]
            ]);

        $this->assertDatabaseHas('categories', [
            'name' => 'Electronics',
            'type' => 'jastip'
        ]);
    }

    public function test_create_category_fails_without_authentication(): void
    {
        $response = $this->postJson('/api/v1/categories', [
            'name' => 'Electronics',
            'icon' => '📱',
            'type' => 'jastip',
        ]);

        $response->assertStatus(401);
    }

    public function test_create_category_fails_with_duplicate_name(): void
    {
        $user = User::factory()->create();
        Category::factory()->create(['name' => 'Electronics']);

        $response = $this->actingAs($user)
            ->postJson('/api/v1/categories', [
                'name' => 'Electronics',
                'icon' => '📱',
                'type' => 'jastip',
            ]);

        $response->assertStatus(422);
    }

    public function test_create_category_fails_with_invalid_type(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->postJson('/api/v1/categories', [
                'name' => 'Electronics',
                'icon' => '📱',
                'type' => 'invalid_type',
            ]);

        $response->assertStatus(422);
    }

    public function test_authenticated_user_can_update_category(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->create();

        $response = $this->actingAs($user)
            ->putJson("/api/v1/categories/{$category->id}", [
                'name' => 'Updated Category',
                'icon' => '🎮',
                'type' => 'preloved',
            ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data'
            ]);

        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
            'name' => 'Updated Category'
        ]);
    }

    public function test_update_category_fails_without_authentication(): void
    {
        $category = Category::factory()->create();

        $response = $this->putJson("/api/v1/categories/{$category->id}", [
            'name' => 'Updated Category',
            'icon' => '🎮',
            'type' => 'preloved',
        ]);

        $response->assertStatus(401);
    }

    public function test_update_category_fails_with_invalid_id(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->putJson('/api/v1/categories/99999', [
                'name' => 'Updated Category',
                'icon' => '🎮',
                'type' => 'preloved',
            ]);

        $response->assertStatus(404);
    }

    public function test_authenticated_user_can_delete_category(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->create();

        $response = $this->actingAs($user)
            ->deleteJson("/api/v1/categories/{$category->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message'
            ]);

        $this->assertDatabaseMissing('categories', [
            'id' => $category->id
        ]);
    }

    public function test_delete_category_fails_without_authentication(): void
    {
        $category = Category::factory()->create();

        $response = $this->deleteJson("/api/v1/categories/{$category->id}");

        $response->assertStatus(401);
    }

    public function test_delete_category_fails_with_invalid_id(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->deleteJson('/api/v1/categories/99999');

        $response->assertStatus(404);
    }
}
