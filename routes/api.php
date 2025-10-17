<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// AI Notification Routes
Route::prefix('products')->group(function () {
    Route::get('/{product}/ai-notifications', [\App\Http\Controllers\Admin\Maintenance\MaintenanceController::class, 'generateAINotifications'])
        ->name('api.products.ai-notifications');
});

// Plant Maintenance Chat Routes
Route::prefix('chat')->group(function () {
    Route::post('/', [\App\Http\Controllers\Api\ChatController::class, 'chat'])
        ->name('api.chat.ask');
    Route::get('/health', [\App\Http\Controllers\Api\ChatController::class, 'health'])
        ->name('api.chat.health');
    Route::get('/suggestions', [\App\Http\Controllers\Api\ChatController::class, 'suggestions'])
        ->name('api.chat.suggestions');
});

// Toggle like/dislike for a statute
Route::middleware('auth:sanctum')->post('/statutes/{statute}/reaction', [\App\Http\Controllers\StatuteReactionController::class, 'toggle']);
