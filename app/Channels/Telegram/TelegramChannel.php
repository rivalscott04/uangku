<?php

namespace App\Channels\Telegram;

use App\Core\MessageProcessor;
use App\Models\Category;
use App\Models\User;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Telegram Channel Adapter
 *
 * Tanggung jawab:
 * - Menerima payload update dari Telegram.
 * - Normalisasi data menjadi bentuk generik (text, user, context).
 * - Meneruskan ke Core\MessageProcessor.
 * - Mengkonversi hasil core menjadi respon yang valid untuk Telegram.
 */
class TelegramChannel
{
    public function __construct(
        protected MessageProcessor $messageProcessor,
    ) {
    }

    /**
     * Handle update webhook dari Telegram.
     *
     * @param array $update Payload mentah dari Telegram.
     * @return array Payload response ke Telegram (akan dikonversi ke JSON).
     */
    public function handleIncoming(array $update): array
    {
        $text = trim((string) data_get($update, 'message.text', ''));

        // Untuk MVP: pakai user demo berbasis telegram user id,
        // memanfaatkan kolom bawaan users (email, name) dulu.
        $telegramId = (string) data_get($update, 'message.from.id');
        $name = (string) data_get($update, 'message.from.first_name', 'User');

        // 1. Jika sudah pernah di-link, pakai user tersebut.
        $user = null;
        if ($telegramId !== '') {
            $user = User::where('telegram_id', $telegramId)->first();
        }

        $chatId = data_get($update, 'message.chat.id');

        // 2. Jika pesan pertama berupa /start <token>, coba link-kan ke user yang sudah register via web.
        if (! $user && $telegramId !== '' && Str::startsWith($text, '/start')) {
            $parts = explode(' ', $text, 2);
            $token = $parts[1] ?? null;

            if ($token) {
                try {
                    // Token mungkin ter-encode di URL, coba decode dulu
                    $decodedToken = urldecode($token);
                    $userId = Crypt::decryptString($decodedToken);
                    $linkedUser = User::find($userId);
                    
                    Log::debug('Telegram token processing', [
                        'token_raw' => substr($token, 0, 20) . '...',
                        'token_decoded' => substr($decodedToken, 0, 20) . '...',
                        'user_id_decrypted' => $userId,
                    ]);

                    if ($linkedUser) {
                        $linkedUser->telegram_id = $telegramId;
                        $linkedUser->save();

                        Log::info('Telegram account linked', [
                            'user_id' => $linkedUser->id,
                            'telegram_id' => $telegramId,
                            'email' => $linkedUser->email,
                        ]);

                        $user = $linkedUser;

                        // Ambil daftar kategori global yang tersedia
                        $categories = Category::whereNull('user_id')
                            ->orderBy('name')
                            ->pluck('name')
                            ->all();

                        $categoryLine = '';
                        if (! empty($categories)) {
                            $categoryLine = "\n\nDi sistem sudah ada beberapa kategori bawaan:\n- " . implode("\n- ", $categories);
                        }

                        // Kirim pesan konfirmasi linking berhasil tanpa masuk ke MessageProcessor.
                        return [
                            'method'  => 'sendMessage',
                            'chat_id' => $chatId,
                            'text'    => 'Akun Telegram kamu sudah terhubung dengan Uangku. Sekarang kamu bisa kirim catatan keuangan di sini.' . $categoryLine,
                        ];
                    } else {
                        Log::warning('Telegram linking failed: User not found', [
                            'user_id' => $userId,
                            'telegram_id' => $telegramId,
                            'token' => substr($token, 0, 10) . '...',
                        ]);
                    }
                } catch (\Throwable $e) {
                    // Jika token invalid atau gagal didekripsi, coba tanpa decode
                    try {
                        $userId = Crypt::decryptString($token);
                        $linkedUser = User::find($userId);
                        
                        if ($linkedUser) {
                            $linkedUser->telegram_id = $telegramId;
                            $linkedUser->save();
                            
                            Log::info('Telegram account linked (without decode)', [
                                'user_id' => $linkedUser->id,
                                'telegram_id' => $telegramId,
                                'email' => $linkedUser->email,
                            ]);
                            
                            $user = $linkedUser;
                            
                            $categories = Category::whereNull('user_id')
                                ->orderBy('name')
                                ->pluck('name')
                                ->all();
                            
                            $categoryLine = '';
                            if (! empty($categories)) {
                                $categoryLine = "\n\nDi sistem sudah ada beberapa kategori bawaan:\n- " . implode("\n- ", $categories);
                            }
                            
                            return [
                                'method'  => 'sendMessage',
                                'chat_id' => $chatId,
                                'text'    => 'Akun Telegram kamu sudah terhubung dengan Uangku. Sekarang kamu bisa kirim catatan keuangan di sini.' . $categoryLine,
                            ];
                        }
                    } catch (\Throwable $e2) {
                        // Jika token invalid atau gagal didekripsi, lanjut ke fallback di bawah.
                        Log::error('Telegram linking failed: Token decrypt error (both methods)', [
                            'error1' => $e->getMessage(),
                            'error2' => $e2->getMessage(),
                            'telegram_id' => $telegramId,
                            'token_preview' => $token ? substr($token, 0, 20) . '...' : 'null',
                        ]);
                    }
                }
            } else {
                // Fallback: kalau /start tanpa token, coba auto-link ke user yang baru daftar
                // (dalam 10 menit terakhir) yang belum punya telegram_id
                $recentUser = User::whereNull('telegram_id')
                    ->where('created_at', '>=', now()->subMinutes(10))
                    ->orderBy('created_at', 'desc')
                    ->first();

                if ($recentUser) {
                    $recentUser->telegram_id = $telegramId;
                    $recentUser->save();

                    Log::info('Telegram account auto-linked (fallback)', [
                        'user_id' => $recentUser->id,
                        'telegram_id' => $telegramId,
                        'email' => $recentUser->email,
                    ]);

                    $user = $recentUser;

                    // Ambil daftar kategori global yang tersedia
                    $categories = Category::whereNull('user_id')
                        ->orderBy('name')
                        ->pluck('name')
                        ->all();

                    $categoryLine = '';
                    if (! empty($categories)) {
                        $categoryLine = "\n\nDi sistem sudah ada beberapa kategori bawaan:\n- " . implode("\n- ", $categories);
                    }

                    return [
                        'method'  => 'sendMessage',
                        'chat_id' => $chatId,
                        'text'    => 'Akun Telegram kamu sudah terhubung dengan Uangku. Sekarang kamu bisa kirim catatan keuangan di sini.' . $categoryLine,
                    ];
                }

                Log::info('Telegram /start tanpa token dan tidak ada user baru', [
                    'telegram_id' => $telegramId,
                    'text' => $text,
                ]);
            }
        }

        // 3. Fallback: buat pseudo-user berbasis telegram id (untuk pengguna yang belum register via web).
        if (! $user) {
            $user = User::firstOrCreate(
                ['email' => "tg_{$telegramId}@example.test"],
                [
                    'name'     => $name,
                    'password' => bcrypt('secret'), // placeholder, tidak dipakai login biasa
                ]
            );
        }

        $context = [
            'channel'       => 'telegram',
            'chat_id'       => $chatId,
            'telegram_raw'  => $update,
        ];

        $result = $this->messageProcessor->process($user, $text, $context);

        // Konversi respon generik core menjadi format Telegram sederhana.
        if (($result['type'] ?? 'text') === 'text') {
            return [
                'method' => 'sendMessage',
                'chat_id' => $context['chat_id'],
                'text' => $result['message'] ?? 'OK',
            ];
        }

        // Fallback sangat sederhana
        return [
            'method' => 'sendMessage',
            'chat_id' => $context['chat_id'],
            'text' => 'Pesan kamu sudah diproses 👍',
        ];
    }
}


