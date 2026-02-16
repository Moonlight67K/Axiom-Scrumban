<?php

namespace App\Services;

use App\Models\Task;
use App\Models\Column;
use Illuminate\Support\Facades\DB;

class TaskService
{
    public function createTask(array $data): Task
    {
        // Simple positioning logic: add to end of column
        if (!isset($data['position'])) {
            $maxPos = Task::where('column_id', $data['column_id'])->max('position');
            $data['position'] = $maxPos ? $maxPos + 1000 : 1000;
        }
        
        return Task::create($data);
    }

    public function moveTask(Task $task, string $targetColumnId, int $newPosition)
    {
        // For Day 1, simple update. Later: rebalancing.
        $task->update([
            'column_id' => $targetColumnId,
            'position' => $newPosition
        ]);
        
        return $task;
    }
}
