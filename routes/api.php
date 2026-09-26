<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\TaskController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public Authentication Routes (Throttled for Security)
|--------------------------------------------------------------------------
*/
Route::middleware(['throttle:15,1'])->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
});

/*
|--------------------------------------------------------------------------
| Protected Application Routes (Token-Based Security / JWT)
|--------------------------------------------------------------------------
*/
Route::middleware(['jwt.auth', 'throttle:120,1'])->group(function () {
    // Current User Profile & Quick Stats
    Route::get('/me', [AuthController::class, 'me']);
    Route::get('/user', [AuthController::class, 'me']); // alias for standard convention
    Route::post('/logout', [AuthController::class, 'logout']);

    // Categories CRUD
    Route::apiResource('categories', CategoryController::class);

    // Tasks CRUD & Additional endpoints
    Route::get('/tasks-stats', [TaskController::class, 'stats']);
    Route::patch('/tasks/{id}/status', [TaskController::class, 'toggleStatus']);
    Route::apiResource('tasks', TaskController::class);
});
