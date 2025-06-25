<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\ChatController;


Route::get('/', function () { return redirect()->route('login');});

Auth::routes();

Route::middleware('auth')->group(function () {
    Route::get('/users', [ChatController::class, 'index'])->name('users');
    Route::get('/chat/{receiverId}', [ChatController::class, 'chat'])->name('chat');
    Route::post('/chat/{receiverId}/send', [ChatController::class, 'sendMessage']);
    Route::post("/chat/typing", [ChatController::class, 'typing']);
    Route::post("/online", [ChatController::class, 'isOnline']);
    Route::post("/offline", [ChatController::class, 'isOffline']);
});