<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware(['auth', 'verified'])->group(function () {

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Task Assignment
    Route::get('/taskassignment', [DashboardController::class, 'taskAssignment'])->name('taskassignment');

    // Kanban Boards Teams
    Route::get('/kanbanteams', [DashboardController::class, 'kanbanTeams'])->name('kanbanteams');

    // Task Progress Updates
    Route::get('/taskprogress', [DashboardController::class, 'taskProgress'])->name('taskprogress');

    // Reminders Implementation
    Route::get('/reminders', [DashboardController::class, 'reminders'])->name('reminders');

    // Profile routes
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';

