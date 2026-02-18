<?php

namespace App\Http\Controllers;

use App\Models\Board;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Gate;

class BoardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        if ($user->email === 'admin@example.com') {
            $boards = Board::all();
        }
        else {
            $boards = $user->boards;
        }

        return Inertia::render('Board/Index', [
            'boards' => $boards
        ]);
    }

    public function show($id)
    {
        $board = Board::with([
            'columns.cards.assignee',
            'columns.cards.dependencies',
            'members'
        ])->findOrFail($id);

        Gate::authorize('view', $board);

        $users = \App\Models\User::all(['id', 'name', 'email']);

        // Check if current user is admin
        $isAdmin = auth()->user()->email === 'admin@example.com';

        return Inertia::render('Board/Show', [
            'board' => $board,
            'users' => $users,
            'isAdmin' => $isAdmin,
        ]);
    }

    public function addMember(Request $request, $id)
    {
        $board = Board::findOrFail($id);
        Gate::authorize('update', $board);

        $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $board->members()->syncWithoutDetaching([$request->user_id]);

        return back();
    }

    public function removeMember(Request $request, $id, $userId)
    {
        $board = Board::findOrFail($id);
        Gate::authorize('update', $board);

        // Don't allow removing yourself if you are the admin (or maybe just keep it simple)
        $board->members()->detach($userId);

        return back();
    }

    public function moveCard(Request $request, $id)
    {
        // ... (existing move logic, maybe add authorization later)
        // Keep it simple for now as per user request to move card when status changes
        // but drag and drop should also be checked.

        $card = \App\Models\Card::findOrFail($request->card_id);
        Gate::authorize('update', $card);

        $request->validate([
            'card_id' => 'required|exists:cards,id',
            'to_column_id' => 'required|exists:columns,id',
            'new_position' => 'required|integer',
        ]);

        $fromColumnId = $card->column_id;
        $toColumn = \App\Models\Column::findOrFail($request->to_column_id);
        $oldPosition = $card->position;
        $newPosition = $request->new_position;

        // WIP Guard: Check if the destination column is full
        if ($fromColumnId !== $toColumn->id && $toColumn->wip_limit && $toColumn->cards()->count() >= $toColumn->wip_limit) {
            return back()->withErrors(['message' => "WIP Limit reached for column: {$toColumn->name}"]);
        }

        // Update positions of other cards in the destination column
        \App\Models\Card::where('column_id', $toColumn->id)
            ->where('position', '>=', $newPosition)
            ->increment('position');

        $card->update([
            'column_id' => $toColumn->id,
            'position' => $newPosition,
        ]);

        // Clean up positions in the source column if it was a move between columns
        if ($fromColumnId !== $toColumn->id) {
            \App\Models\Card::where('column_id', $fromColumnId)
                ->where('position', '>', $oldPosition)
                ->decrement('position');
        }

        // Track history for analytics (CFD)
        if ($fromColumnId !== $toColumn->id) {
            \Illuminate\Support\Facades\DB::table('card_history')->insert([
                'card_id' => $card->id,
                'column_id' => $toColumn->id,
                'transitioned_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Broadcast the move
        broadcast(new \App\Events\CardMoved(
            $card->id,
            $fromColumnId,
            $request->to_column_id,
            $request->new_position
            ))->toOthers();

        return back();
    }

    public function storeCard(Request $request, $boardId)
    {
        $board = Board::with('columns')->findOrFail($boardId);
        Gate::authorize('view', $board);

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'user_id' => 'nullable|exists:users,id',
            'priority' => 'nullable|string|in:low,medium,high,urgent',
            'due_date' => 'nullable|date',
        ]);

        // Find the "To Do" column for this board (status: not_started)
        $column = $board->columns()->where('status', 'not_started')->first();

        if (!$column) {
            // Fallback if seeding wasn't run or columns missing
            $column = $board->columns()->first();
        }

        if ($column->wip_limit && $column->cards()->count() >= $column->wip_limit) {
            return back()->withErrors(['message' => "Cannot add card: WIP Limit reached for {$column->name}"]);
        }

        $position = $column->cards()->max('position') + 1;

        $card = \App\Models\Card::create([
            'column_id' => $request->column_id,
            'user_id' => $request->user_id,
            'title' => $request->title,
            'description' => $request->description,
            'priority' => $request->priority ?? 'medium',
            'due_date' => $request->due_date,
            'position' => $position,
        ]);

        if ($request->has('dependencies')) {
            $card->dependencies()->sync($request->dependencies);
        }

        return back();
    }

    public function updateCard(Request $request, $boardId, $cardId)
    {
        $card = \App\Models\Card::findOrFail($cardId);
        Gate::authorize('update', $card);

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'user_id' => 'nullable|exists:users,id',
            'priority' => 'required|string|in:low,medium,high,urgent',
            'due_date' => 'nullable|date',
            'dependencies' => 'nullable|array',
            'dependencies.*' => 'exists:cards,id',
            'status' => 'nullable|string',
        ]);

        $isAdmin = auth()->user()->email === 'admin@example.com';

        if ($isAdmin) {
            $updateData = [
                'title' => $request->title,
                'description' => $request->description,
                'user_id' => $request->user_id,
                'priority' => $request->priority,
                'due_date' => $request->due_date,
            ];
        }
        else {
            // Regular users can only trigger status changes (which handles column moves)
            $updateData = [];
        }

        // Automatic Movement based on status
        if ($request->has('status') && $request->status) {
            $newColumn = \App\Models\Column::where('board_id', $boardId)
                ->where('status', $request->status)
                ->first();

            if ($newColumn && $newColumn->id !== $card->column_id) {
                // Check WIP Limit
                if ($newColumn->wip_limit && $newColumn->cards()->count() >= $newColumn->wip_limit) {
                    return back()->withErrors(['message' => "Cannot move card to {$newColumn->name}: WIP Limit reached."]);
                }

                $updateData['column_id'] = $newColumn->id;
                $updateData['position'] = $newColumn->cards()->max('position') + 1;
            }
        }

        $card->update($updateData);

        if ($request->has('dependencies')) {
            $card->dependencies()->sync($request->dependencies);
        }

        return back();
    }

    public function storeColumn(Request $request, $boardId)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'wip_limit' => 'nullable|integer|min:0',
        ]);

        $board = Board::findOrFail($boardId);
        $position = $board->columns()->max('position') + 1;

        \App\Models\Column::create([
            'board_id' => $boardId,
            'name' => $request->name,
            'wip_limit' => $request->wip_limit,
            'position' => $position,
        ]);

        return back();
    }

    public function cfdData(Board $board)
    {
        $data = \Illuminate\Support\Facades\DB::table('card_history')
            ->join('columns', 'card_history.column_id', '=', 'columns.id')
            ->where('columns.board_id', $board->id)
            ->select(
            \Illuminate\Support\Facades\DB::raw('DATE(transitioned_at) as date'),
            'columns.name as column',
            \Illuminate\Support\Facades\DB::raw('count(*) as count')
        )
            ->groupBy('date', 'column')
            ->orderBy('date')
            ->get();

        return response()->json($data);
    }
}
