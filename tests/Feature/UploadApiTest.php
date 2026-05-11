<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class UploadApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_upload_image()
    {
        $user = User::factory()->create();

        Storage::fake('public');

        $file = UploadedFile::fake()->image('macbook-bekas.jpg');

        $response = $this->actingAs($user)->postJson('/api/v1/upload', [
            'image' => $file,
        ]);

        $response->assertStatus(201)
                 ->assertJsonStructure([
                     'success',
                     'message',
                     'data' => [
                         'image_url'
                     ]
                 ]);

        $imageUrl = $response->json('data.image_url');
        $savedFileName = basename($imageUrl);

        $this->assertTrue(Storage::disk('public')->exists('uploads/' . $savedFileName));    
    }

    public function test_unauthenticated_user_cannot_upload_image()
    {
        Storage::fake('public');
        $file = UploadedFile::fake()->image('hacker-image.jpg');

        $response = $this->postJson('/api/v1/upload', [
            'image' => $file,
        ]);

        $response->assertStatus(401);
    }

    public function test_upload_fails_if_file_is_too_large()
    {
        $user = User::factory()->create();
        Storage::fake('public');

        $file = UploadedFile::fake()->image('giant-image.jpg')->size(6000);

        $response = $this->actingAs($user)->postJson('/api/v1/upload', [
            'image' => $file,
        ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['image']);
    }

    public function test_upload_fails_if_file_is_not_an_image()
    {
        $user = User::factory()->create();
        Storage::fake('public');

        $file = UploadedFile::fake()->create('malware.pdf', 100, 'application/pdf');

        $response = $this->actingAs($user)->postJson('/api/v1/upload', [
            'image' => $file,
        ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['image']);
    }
}