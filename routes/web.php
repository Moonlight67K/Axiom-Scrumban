<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
    'canLogin' => Route::has('login'),
    'canRegister' => Route::has('register'),
    'laravelVersion' => Application::VERSION,
    'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class , 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class , 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class , 'destroy'])->name('profile.destroy');

    Route::get('/boards', [App\Http\Controllers\BoardController::class , 'index'])->name('boards.index');
    Route::get('/boards/{id}', [App\Http\Controllers\BoardController::class , 'show'])->name('boards.show');
    Route::post('/boards/{id}/move-card', [App\Http\Controllers\BoardController::class , 'moveCard'])->name('boards.move-card');
    Route::post('/boards/{id}/cards', [App\Http\Controllers\BoardController::class , 'storeCard'])->name('boards.store-card');
    Route::patch('/boards/{id}/cards/{card_id}', [App\Http\Controllers\BoardController::class , 'updateCard'])->name('boards.update-card');
    Route::post('/boards/{id}/members', [App\Http\Controllers\BoardController::class , 'addMember'])->name('boards.add-member');
    Route::delete('/boards/{id}/members/{user_id}', [App\Http\Controllers\BoardController::class , 'removeMember'])->name('boards.remove-member');
    Route::post('/boards/{id}/columns', [App\Http\Controllers\BoardController::class , 'storeColumn'])->name('boards.store-column');
    Route::get('/boards/{id}/cfd', [App\Http\Controllers\BoardController::class , 'cfdData'])->name('boards.cfd');
});

require __DIR__ . '/auth.php';
