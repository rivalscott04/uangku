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


