<?php

namespace App\Http\Controllers;

use App\Channels\Telegram\TelegramChannel;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelegramWebhookController extends Controller
{
    public function __construct(
        protected TelegramChannel $telegramChannel,
    ) {
    }

    /**
     * Endpoint webhook Telegram.
     *
     * Telegram akan mengirimkan JSON payload ke endpoint ini.
     */
    public function __invoke(Request $request): JsonResponse
    {
        $update = $request->all();

        // Log incoming webhook untuk debugging
        Log::debug('Telegram webhook received', [
            'update_id' => $update['update_id'] ?? null,
            'message_text' => $update['message']['text'] ?? null,
            'from_id' => $update['message']['from']['id'] ?? null,
        ]);

        try {
            $responsePayload = $this->telegramChannel->handleIncoming($update);

            // Ambil konfigurasi bot Telegram dari config/services.php
            $botToken = (string) Config::get('services.telegram.bot_token');
            $apiBase = rtrim((string) Config::get('services.telegram.api_url', 'https://api.telegram.org'), '/');

            if ($botToken !== '' && ($responsePayload['method'] ?? null) === 'sendMessage') {
                // Kirim balasan ke Telegram menggunakan API resmi.
                $response = Http::post("{$apiBase}/bot{$botToken}/sendMessage", [
                    'chat_id' => $responsePayload['chat_id'] ?? null,
                    'text'    => $responsePayload['text'] ?? '',
                ]);

                if (! $response->successful()) {
                    Log::error('Failed to send Telegram message', [
                        'status' => $response->status(),
                        'body' => $response->body(),
                    ]);
                }
            }
        } catch (\Throwable $e) {
            Log::error('Telegram webhook error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }

        // Telegram hanya butuh respon cepat. Kita tidak perlu mengembalikan payload lengkap.
        return response()->json([
            'ok' => true,
        ]);
    }
}


