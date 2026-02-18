<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ExternalBoardController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::group(['prefix' => 'external'], function () {
    Route::get('/boards', [ExternalBoardController::class , 'index']);
    Route::post('/cards', [ExternalBoardController::class , 'createCard']);
});
