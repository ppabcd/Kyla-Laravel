<?php

use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Dashboard Routes
Route::prefix('dashboard')->name('dashboard.')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('index');
    Route::get('/users', [DashboardController::class, 'users'])->name('users');
    Route::get('/conversations', [DashboardController::class, 'conversations'])->name('conversations');
    Route::get('/finances', [DashboardController::class, 'finances'])->name('finances');
    Route::get('/moderation', [DashboardController::class, 'moderation'])->name('moderation');
    Route::get('/analytics', [DashboardController::class, 'analytics'])->name('analytics');

    // API Routes for real-time data
    Route::get('/api/stats', [DashboardController::class, 'apiStats'])->name('api.stats');

    // User management actions
    Route::get('/users/{user}', [DashboardController::class, 'showUser'])->name('users.show');
    Route::post('/users/{user}/ban', [DashboardController::class, 'banUser'])->name('users.ban');
    Route::post('/users/{user}/unban', [DashboardController::class, 'unbanUser'])->name('users.unban');

    // Word filter management
    Route::post('/word-filters', [DashboardController::class, 'storeWordFilter'])->name('word-filters.store');
    Route::put('/word-filters/{id}', [DashboardController::class, 'updateWordFilter'])->name('word-filters.update');
    Route::delete('/word-filters/{id}', [DashboardController::class, 'deleteWordFilter'])->name('word-filters.delete');
    Route::post('/word-filters/bulk-import', [DashboardController::class, 'bulkImportWordFilter'])->name('word-filters.bulk-import');
    Route::get('/word-filters/stats', [DashboardController::class, 'getWordFilterStats'])->name('word-filters.stats');
});
