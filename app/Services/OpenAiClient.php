<?php

namespace App\Services;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;

/**
 * OpenAiClient
 *
 * Service kecil untuk memanggil OpenAI (teks & nanti gambar).
 * Tujuan:
 * - Pisahkan dependency OpenAI dari core engine (MessageProcessor, AiFinanceAgent).
 * - Mudah diganti/dimatikan tanpa mengubah logika bisnis utama.
 *
 * Catatan:
 * - API key dibaca dari config('services.openai.api_key').
 * - Jika key kosong, method akan mengembalikan null / fallback aman.
 */
class OpenAiClient
{
    /**
     * Generate saran budget singkat berbasis konteks pengguna.
     *
     * @param  array  $context
     *   - remaining_percent: int
     *   - remaining_amount: int (dalam rupiah)
     *   - category: string|null
     * @return string|null
     */
    public function generateBudgetAdvice(array $context): ?string
    {
        $apiKey = (string) Config::get('services.openai.api_key', '');
        if ($apiKey === '') {
            return null;
        }

        $baseUrl = rtrim((string) Config::get('services.openai.base_url', 'https://api.openai.com'), '/');
        $model = (string) Config::get('services.openai.default_model', 'gpt-4.1-mini');

        $remainingPercent = $context['remaining_percent'] ?? null;
        $remainingAmount = $context['remaining_amount'] ?? null;
        $category = $context['category'] ?? null;

        $prompt = "Kamu adalah asisten keuangan pribadi yang singkat, santai, dan pakai bahasa Indonesia sehari-hari.\n";
        $prompt .= "Berikan 1–2 kalimat saran singkat berdasarkan konteks budget berikut.\n";
        $prompt .= "Boleh pakai emoji ringan (misalnya 😂, 😊), tapi jangan berlebihan.\n\n";
        $prompt .= "Konteks:\n";
        $prompt .= "- Kategori: " . ($category ?: 'total') . "\n";
        $prompt .= "- Sisa budget (persen): " . ($remainingPercent ?? 'unknown') . "\n";
        $prompt .= "- Sisa budget (rupiah): " . ($remainingAmount ?? 'unknown') . "\n\n";
        $prompt .= "Balas hanya saran, tanpa penjelasan tambahan.";

        $response = Http::withToken($apiKey)
            ->post("{$baseUrl}/v1/chat/completions", [
                'model' => $model,
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => 'Kamu adalah asisten keuangan pribadi yang membantu pengguna mengelola budget.',
                    ],
                    [
                        'role' => 'user',
                        'content' => $prompt,
                    ],
                ],
                'temperature' => 0.7,
            ]);

        if (! $response->ok()) {
            // Untuk sekarang, jangan lempar exception ke user-facing flow.
            return null;
        }

        $data = $response->json();
        $text = $data['choices'][0]['message']['content'] ?? null;

        return is_string($text) ? trim($text) : null;
    }

    /**
     * (Stub) Analisa gambar struk menggunakan OpenAI Vision / multimodal.
     *
     * @param  string  $imageUrl URL atau data gambar yang bisa diakses OpenAI.
     * @param  array   $context  Metadata tambahan (mis. channel, language).
     * @return array|null
     */
    public function analyzeReceiptImage(string $imageUrl, array $context = []): ?array
    {
        $apiKey = (string) Config::get('services.openai.api_key', '');
        if ($apiKey === '') {
            return null;
        }

        $baseUrl = rtrim((string) Config::get('services.openai.base_url', 'https://api.openai.com'), '/');
        $model = (string) Config::get('services.openai.default_model', 'gpt-4.1-mini');

        // Catatan:
        // - Implementasi penuh vision/multimodal tergantung format API yang kamu pakai.
        // - Di sini kita siapkan payload generik yang bisa kamu sesuaikan nanti
        //   (misalnya mengirim base64 image atau URL yang di-allow OpenAI).

        $response = Http::withToken($apiKey)
            ->post("{$baseUrl}/v1/chat/completions", [
                'model' => $model,
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => 'Ekstrak informasi struk belanja Indonesia. Fokus hanya pada nama merchant, tanggal transaksi, dan total akhir.',
                    ],
                    [
                        'role' => 'user',
                        'content' => "Ini adalah gambar struk: {$imageUrl}\n\nBalas dalam format JSON dengan key: merchant_name, transaction_date, total_amount.",
                    ],
                ],
                'temperature' => 0,
            ]);

        if (! $response->ok()) {
            return null;
        }

        $data = $response->json();
        $text = $data['choices'][0]['message']['content'] ?? null;

        if (! is_string($text)) {
            return null;
        }

        // Backend bebas melakukan json_decode di layer pemanggil.
        return [
            'raw' => $data,
            'parsed_text' => $text,
        ];
    }
}


