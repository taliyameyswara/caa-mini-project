<?php

use App\Http\Controllers\AgentController;
use App\Http\Controllers\ChatRoomController;
use Illuminate\Support\Facades\Route;

Route::get('/agents', [AgentController::class, 'index']);
Route::get('/agents/{id}', [AgentController::class, 'updateMaxCustomers']);

Route::get('/chat-rooms', [ChatRoomController::class, 'index']);
Route::post('/webhook/qiscus', [ChatRoomController::class, 'qiscusWebhook']);
