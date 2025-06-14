<?php

use App\Http\Controllers\ChatController;
use App\Http\Controllers\ChatMessageController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::resource('chats', ChatController::class);
Route::post('chats/{chat}/messages', [ChatMessageController::class, 'store'])->name('chat-messages.store');
