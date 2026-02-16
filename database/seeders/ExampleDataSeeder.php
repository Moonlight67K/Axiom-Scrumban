<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Organization;
use App\Models\User;
use App\Models\Board;
use App\Models\Column;
use App\Models\Task;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

class ExampleDataSeeder extends Seeder
{
    public function run()
    {
        // Create example organization
        $org = Organization::create([
            'name' => 'Acme Corp',
            'schema_name' => 'acme'
        ]);

        // Create admin user
        $admin = User::create([
            'organization_id' => $org->id,
            'name' => 'Admin User',
            'email' => 'admin@acme.test',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        // Create a sample board
        $board = Board::create([
            'organization_id' => $org->id,
            'name' => 'Product Board',
            'description' => 'Sample board for product work',
        ]);

        // Create columns
        $todo = Column::create([
            'board_id' => $board->id,
            'organization_id' => $org->id,
            'name' => 'To Do',
            'position' => 1000,
            'wip_limit' => 0,
        ]);

        $doing = Column::create([
            'board_id' => $board->id,
            'organization_id' => $org->id,
            'name' => 'Doing',
            'position' => 2000,
            'wip_limit' => 3,
        ]);

        $done = Column::create([
            'board_id' => $board->id,
            'organization_id' => $org->id,
            'name' => 'Done',
            'position' => 3000,
            'wip_limit' => 0,
        ]);

        // Create sample tasks
        Task::create([
            'column_id' => $todo->id,
            'organization_id' => $org->id,
            'title' => 'Setup project repo',
            'description' => 'Initialize repository and CI',
            'position' => 1000,
            'metadata' => ['priority' => 'high']
        ]);

        Task::create([
            'column_id' => $doing->id,
            'organization_id' => $org->id,
            'title' => 'Implement auth',
            'description' => 'Registration, login, tokens',
            'position' => 1000,
            'metadata' => ['priority' => 'medium']
        ]);

        Task::create([
            'column_id' => $done->id,
            'organization_id' => $org->id,
            'title' => 'Write README',
            'description' => 'Project overview',
            'position' => 1000,
            'metadata' => ['priority' => 'low']
        ]);
    }
}
