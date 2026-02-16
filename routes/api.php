<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BoardController;
use App\Http\Controllers\TaskController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware(['auth:sanctum', 'tenant'])->group(function () {
    Route::get('/user', function (Request $request) {
        $user = $request->user();
        $user->load('organization');
        return response()->json(['user' => $user, 'organization' => $user->organization]);
    });
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::apiResource('boards', BoardController::class);
    Route::apiResource('tasks', TaskController::class);
});
