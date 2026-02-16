<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SeedBoardData extends Command
{
    protected $signature = 'seed:board';
    protected $description = 'Insert sample organization, board, column and task';

    public function handle()
    {
        DB::transaction(function () {

            // Insert organization
            $organizationId = DB::table('organizations')->insertGetId([
                'name' => 'HealthOrg',
                'schema_name' => 'healthorg',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Insert board
            $boardId = DB::table('boards')->insertGetId([
                'organization_id' => $organizationId,
                'name' => 'Development Board',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Insert column (IMPORTANT: change to board_columns if renamed)
            $columnId = DB::table('columns')->insertGetId([
                'organization_id' => $organizationId,
                'board_id' => $boardId,
                'name' => 'To Do',
                'wip_limit' => 5,
                'position' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Insert task
            DB::table('tasks')->insert([
                'organization_id' => $organizationId,
                'board_id' => $boardId,
                'column_id' => $columnId, // change to board_column_id if needed
                'title' => 'Setup Database',
                'position' => 1,
                'metadata' => json_encode([
                    'priority' => 'high',
                    'assigned_to' => 'John Doe'
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        });

        $this->info('Sample board data inserted successfully!');
    }
}
