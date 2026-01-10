<?php

use App\Http\Controllers\ChatController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use GuzzleHttp\Client;

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

/**
 * Public Chatbot Widget API Routes
 * These routes are used by the embedded chatbot widget
 */
Route::prefix('chatbot')->name('api.chatbot.')->group(function () {

    // Get chatbot configuration
    Route::get('/{chatbotId}/config', [ChatController::class, 'getConfig'])
        ->name('config');

    // Send message to chatbot
    Route::post('/message', [ChatController::class, 'sendMessage'])
        ->middleware(['throttle:60,1'])
        ->name('message');

    // Get conversation messages
    Route::post('/messages', [ChatController::class, 'getMessages'])
        ->middleware(['throttle:60,1'])
        ->name('messages');

    // Upload image to chatbot
    Route::post('/upload-image', [ChatController::class, 'uploadImage'])
        ->middleware(['throttle:10,1'])
        ->name('upload-image');

    // Upload voice message to chatbot
    Route::post('/voice-message', [ChatController::class, 'uploadVoice'])
        ->middleware(['throttle:10,1'])
        ->name('voice-message');

    // Rate conversation
    Route::post('/rate', [ChatController::class, 'rateConversation'])
        ->middleware(['throttle:10,1'])
        ->name('rate');

    // Request human takeover
    Route::post('/request-human', [ChatController::class, 'requestHuman'])
        ->middleware(['throttle:10,1'])
        ->name('request-human');
});






