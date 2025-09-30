<?php

use App\Http\Controllers\AgentController;
use App\Http\Controllers\ChatRoomController;
use App\Http\Controllers\WebhookController;
use Illuminate\Support\Facades\Route;


// Route::get('/qiscus-agents', [AgentController::class, 'getAllQiscusAgents']);
// Route::get('/sync-agents', [AgentController::class, 'syncAgents']);

// Route::post('/webhook/qiscus', [WebhookController::class, 'handle']);
// Route::post('/webhook/qiscus', [WebhookController::class, 'qiscusWebhook']);

Route::get('/agents', [AgentController::class, 'index']);
Route::get('/chat-rooms', [ChatRoomController::class, 'index']);
Route::post('/webhook/qiscus', [ChatRoomController::class, 'qiscusWebhook']);

// catatan: kalo agent dihapus blm bisa sync
// resolved at nya kayak ga sinkron jam nya
