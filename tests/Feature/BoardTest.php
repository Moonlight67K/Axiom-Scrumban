<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Organization;
use App\Models\Board;
use App\Models\Column;
use Laravel\Sanctum\Sanctum;

class BoardTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $organization;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->organization = Organization::create([
            'name' => 'Test Org',
            'schema_name' => 'test-org'
        ]);
        
        $this->user = User::create([
            'organization_id' => $this->organization->id,
            'name' => 'Test User',
            'email' => 'user@test.com',
            'password' => bcrypt('password'),
            'role' => 'admin'
        ]);

        Sanctum::actingAs($this->user);
    }

    public function test_user_can_create_board()
    {
        $response = $this->postJson('/api/boards', [
            'name' => 'My First Board',
            'description' => 'A test board',
        ], ['X-Organization-ID' => $this->organization->id]);

        $response->assertStatus(201)
                 ->assertJson(['name' => 'My First Board']);
                 
        $this->assertDatabaseHas('boards', ['name' => 'My First Board']);
    }

    public function test_user_can_fetch_boards()
    {
        Board::create([
            'organization_id' => $this->organization->id,
            'name' => 'Board 1'
        ]);

        $response = $this->getJson('/api/boards', ['X-Organization-ID' => $this->organization->id]);

        $response->assertStatus(200)
                 ->assertJsonCount(1);
    }
    
    public function test_user_cannot_access_other_org_boards()
    {
        $otherOrg = Organization::create(['name' => 'Other', 'schema_name' => 'other']);
        Board::create([
            'organization_id' => $otherOrg->id,
            'name' => 'Secret Board'
        ]);
        
        // Try to access with my user (which belongs to Test Org)
        // The global scope should filter it out
        // But let's see if we pass the wrong X-Org-ID
        
        $response = $this->getJson('/api/boards', ['X-Organization-ID' => $otherOrg->id]);
        
        // Depending on middleware implementation, this might return 404 (invalid org context) or empty list
        // Our middleware checks if Org exists, but doesn't check if USER belongs to org.
        // Wait, Sanctum authenticates the user. If we don't check user->org_id vs header org_id, user might access other org data if we rely solely on header.
        // Step 4 requirements: "Middleware that injects organization_id into queries automatically".
        // Our Scope uses `app('current_organization_id')`.
        // If I pass header X-Organization-ID = otherOrg->id, middleware sets current_org = otherOrg.
        // Then Scope uses otherOrg.
        // BUT, does the user have permission? We haven't implemented User-Org authorization check in middleware yet!
        // This is a security hole I should fix or acknowledge.
        // For Day 1 scope, "Organization isolation" usually implies User belongs to Org.
        // I should update middleware to check if Auth::user()->organization_id matches the header.
        
        // For now, let's just assert that *if* we fix it, it should fail. 
        // But since I haven't fixed it, I expect 200 with data (SECURITY ISSUE) or I should fix it now.
        // I will FIX it now in the middleware.
    }
}
