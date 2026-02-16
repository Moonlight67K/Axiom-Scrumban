<?php

namespace App\Services;

use App\Models\Board;
use App\Models\Organization;

class BoardService
{
    public function createBoard(Organization $organization, array $data): Board
    {
        return $organization->boards()->create($data);
    }

    public function getBoards(Organization $organization)
    {
        return $organization->boards()->with('columns')->get();
    }

    public function getBoard(string $boardId)
    {
        return Board::with(['columns.tasks', 'sprints'])->findOrFail($boardId);
    }
}
