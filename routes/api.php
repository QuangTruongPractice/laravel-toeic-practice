<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ExamController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::prefix('v1')->group(function () {
    // Auth routes with strict throttle
    Route::middleware('throttle:login')->group(function () {
        Route::post('/register', [AuthController::class, 'register']);
        Route::post('/login', [AuthController::class, 'login']);
    });

    // Public Exam routes
    Route::middleware('throttle:api')->group(function () {
        Route::get('/exams', [ExamController::class, 'index']);
        Route::get('/exams/{exam}', [ExamController::class, 'show']);
    });

    // Authenticated routes
    Route::middleware(['auth:sanctum', 'throttle:api'])->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);

        // Exam attempts
        Route::post('/exams/{exam}/attempt', [ExamController::class, 'startAttempt']);
        Route::post('/attempts/{attempt}/submit', [ExamController::class, 'submitAttempt']);

        // User history
        Route::get('/users/me/history', [UserController::class, 'history']);
    });
});
