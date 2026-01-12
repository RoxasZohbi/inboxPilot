<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\GmailController;
use App\Http\Controllers\Api\EmailController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Public routes (no authentication required)
Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'timestamp' => now()->toIso8601String(),
    ]);
});

// Protected routes (require authentication)
Route::middleware('auth:sanctum')->group(function () {
    // Get authenticated user
    Route::get('/user', function (Request $request) {
        return response()->json([
            'data' => $request->user(),
        ]);
    });

    // Category API endpoints
    // Route::apiResource('categories', CategoryController::class);
    
    // Gmail API endpoints
    Route::post('/gmail/sync', [GmailController::class, 'syncEmails'])->middleware('throttle:5,1');
    Route::get('/gmail/sync-status', [GmailController::class, 'syncStatus']);
    Route::post('/emails/process-pending', [GmailController::class, 'processPendingEmails'])->middleware('throttle:5,1');
    
    // Email API endpoints
    Route::get('/emails', [EmailController::class, 'index']);
    Route::get('/emails/{email}', [EmailController::class, 'show']);
    Route::patch('/emails/{id}/toggle-read', [EmailController::class, 'toggleRead']);
    Route::patch('/emails/{id}/toggle-star', [EmailController::class, 'toggleStar']);
    Route::delete('/emails/{id}', [EmailController::class, 'destroy']);
    Route::post('/emails/bulk-delete', [EmailController::class, 'bulkDelete'])->middleware('throttle:60,1');
});
