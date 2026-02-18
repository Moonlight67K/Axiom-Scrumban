<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Board;
use App\Models\Card;
use Illuminate\Http\Request;

class ExternalBoardController extends Controller
{
    /**
     * Get board summary for external systems.
     */
    public function index()
    {
        return response()->json(Board::all());
    }

    /**
     * Create a card from an external webhook (e.g., Salesforce).
     */
    public function createCard(Request $request)
    {
        $request->validate([
            'column_id' => 'required|exists:columns,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'external_id' => 'nullable|string',
        ]);

        $card = Card::create([
            'column_id' => $request->column_id,
            'title' => $request->title,
            'description' => $request->description,
            'metadata' => ['external_source' => $request->external_source, 'external_id' => $request->external_id],
        ]);

        return response()->json($card, 201);
    }
}
