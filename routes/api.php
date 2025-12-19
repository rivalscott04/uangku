<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ReceiptController;

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

Route::post('/receipts/parse', [ReceiptController::class, 'parse'])
    ->name('api.receipts.parse');

Route::post('/receipts/confirm', [ReceiptController::class, 'confirm'])
    ->name('api.receipts.confirm');

// Webhook Telegram (MVP) - di api.php agar tidak pakai CSRF
// Note: URL akan jadi /api/webhook/telegram, perlu update webhook URL di Telegram
Route::post('/webhook/telegram', \App\Http\Controllers\TelegramWebhookController::class)
    ->name('webhook.telegram');


