<?php

namespace Database\Seeders;

use App\Models\Board;
use App\Models\Column;
use App\Models\Card;
use Illuminate\Database\Seeder;

class BoardSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $board = Board::create([
            'name' => 'Main Dev Board',
            'description' => 'Axiom Scrumban main development board.',
        ]);

        $todo = Column::create([
            'board_id' => $board->id,
            'name' => 'To Do',
            'position' => 0,
            'status' => 'not_started',
            'wip_limit' => 5,
        ]);

        $doing = Column::create([
            'board_id' => $board->id,
            'name' => 'In Progress',
            'position' => 1,
            'status' => 'in_progress',
            'wip_limit' => 3,
        ]);

        $completed = Column::create([
            'board_id' => $board->id,
            'name' => 'Completed',
            'position' => 2,
            'status' => 'completed',
        ]);

        $blocked = Column::create([
            'board_id' => $board->id,
            'name' => 'Blocked',
            'position' => 3,
            'status' => 'blocked',
        ]);

        Card::create([
            'column_id' => $todo->id,
            'title' => 'Initial Setup',
            'description' => 'Complete the core project scaffolding.',
        ]);

        Card::create([
            'column_id' => $doing->id,
            'title' => 'Implement WIP Guard',
            'description' => 'Enforce column limits in the backend.',
        ]);
    }
}
