<?php

namespace App\Http\Controllers;

use App\Models\Board;
use App\Models\Organization;
use App\Services\BoardService;
use Illuminate\Http\Request;

class BoardController extends Controller
{
    protected $boardService;

    public function __construct(BoardService $boardService)
    {
        $this->boardService = $boardService;
    }

    public function index(Request $request)
    {
        $orgId = app('current_organization_id');
        $org = Organization::findOrFail($orgId);
        return response()->json($this->boardService->getBoards($org));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $orgId = app('current_organization_id');
        $org = Organization::findOrFail($orgId);

        $board = $this->boardService->createBoard($org, $data);

        return response()->json($board, 201);
    }

    public function show($id)
    {
        return response()->json($this->boardService->getBoard($id));
    }
}
