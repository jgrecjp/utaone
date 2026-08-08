<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_health_is_public(): void
    {
        $this->getJson('/health')->assertOk()->assertJson(['status' => 'ok']);
    }

    public function test_admin_requires_token(): void
    {
        $this->getJson('/v1/admin/songs')->assertUnauthorized()->assertJson(['detail' => 'Invalid admin token']);
    }

    public function test_admin_can_create_and_list_song(): void
    {
        $headers = ['Authorization' => 'Bearer test-admin-token'];
        $this->postJson('/v1/admin/songs', ['title' => 'Test Song', 'artist' => 'Test Artist', 'difficulty' => 5], $headers)->assertOk()->assertJson(['title' => 'Test Song', 'status' => 'draft']);
        $this->getJson('/v1/admin/songs', $headers)->assertOk()->assertJsonCount(1)->assertJsonPath('0.artist', 'Test Artist');
    }

    public function test_public_list_excludes_drafts(): void
    {
        $headers = ['Authorization' => 'Bearer test-admin-token'];
        $this->postJson('/v1/admin/songs', ['title' => 'Draft', 'artist' => 'Artist'], $headers)->assertOk();
        $this->getJson('/v1/songs')->assertOk()->assertExactJson([]);
    }
}
