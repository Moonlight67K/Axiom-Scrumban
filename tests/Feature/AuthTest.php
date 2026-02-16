<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Organization;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_register_and_create_organization()
    {
        $response = $this->postJson('/api/register', [
            'name' => 'Test Admin',
            'email' => 'admin@test.com',
            'password' => 'password',
            'organization_name' => 'Test Org'
        ]);

        $response->assertStatus(201)
                 ->assertJsonStructure(['user', 'organization']);

        $this->assertDatabaseHas('organizations', ['name' => 'Test Org']);
        $this->assertDatabaseHas('users', ['email' => 'admin@test.com']);
    }

    public function test_user_can_login()
    {
        $org = Organization::create(['name' => 'Test Org', 'schema_name' => 'test-org']);
        $user = User::create([
            'organization_id' => $org->id,
            'name' => 'Test User',
            'email' => 'user@test.com',
            'password' => bcrypt('password'),
            'role' => 'admin'
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'user@test.com',
            'password' => 'password'
        ]);

        $response->assertStatus(200)
                 ->assertJsonStructure(['token']);
    }
}
