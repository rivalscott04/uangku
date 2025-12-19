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
                        // Handle konflik: cek apakah user sudah punya telegram_id atau telegram_id sudah dipakai
                        $linkResult = $this->handleTelegramLinking($linkedUser, $telegramId, $chatId);
                        
                        if ($linkResult) {
                            // Jika berhasil link, return response
                            $user = $linkedUser;
                            return $linkResult;
                        }
                        // Jika gagal (konflik), lanjut ke fallback atau error message
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
                            // Handle konflik: cek apakah user sudah punya telegram_id atau telegram_id sudah dipakai
                            $linkResult = $this->handleTelegramLinking($linkedUser, $telegramId, $chatId);
                            
                            if ($linkResult) {
                                // Jika berhasil link, return response
                                $user = $linkedUser;
                                return $linkResult;
                            }
                            // Jika gagal (konflik), lanjut ke fallback atau error message
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
                    // Handle konflik: cek apakah telegram_id sudah dipakai user lain
                    $linkResult = $this->handleTelegramLinking($recentUser, $telegramId, $chatId);
                    
                    if ($linkResult) {
                        // Jika berhasil link, return response
                        $user = $recentUser;
                        return $linkResult;
                    }
                    // Jika gagal (konflik), lanjut ke log
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
            
            // Pastikan telegram_id di-set untuk pseudo-user
            if ($telegramId !== '' && $user->telegram_id !== $telegramId) {
                // Cek dulu apakah telegram_id sudah dipakai user lain
                $existingUser = User::where('telegram_id', $telegramId)->first();
                if (! $existingUser) {
                    $user->telegram_id = $telegramId;
                    $user->save();
                }
            }
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

    /**
     * Handle linking telegram_id ke user dengan validasi konflik.
     *
     * @param User $user User yang akan di-link
     * @param string $telegramId Telegram ID yang akan di-link
     * @param mixed $chatId Chat ID untuk response
     * @return array|null Response array jika berhasil, null jika ada konflik
     */
    protected function handleTelegramLinking(User $user, string $telegramId, $chatId): ?array
    {
        // Skenario 1: User sudah punya telegram_id yang sama -> sudah terhubung
        if ($user->telegram_id === $telegramId) {
            Log::info('Telegram account already linked to same user', [
                'user_id' => $user->id,
                'telegram_id' => $telegramId,
                'email' => $user->email,
            ]);

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
                'text'    => 'Akun Telegram kamu sudah terhubung dengan akun ini. Kamu bisa langsung kirim catatan keuangan di sini.' . $categoryLine,
            ];
        }

        // Skenario 2: User sudah punya telegram_id yang berbeda -> konflik!
        if ($user->telegram_id !== null && $user->telegram_id !== $telegramId) {
            Log::warning('Telegram linking conflict: User already has different telegram_id', [
                'user_id' => $user->id,
                'existing_telegram_id' => $user->telegram_id,
                'new_telegram_id' => $telegramId,
                'email' => $user->email,
            ]);

            return [
                'method'  => 'sendMessage',
                'chat_id' => $chatId,
                'text'    => 'Akun email ' . $user->email . ' sudah terhubung dengan Telegram ID lain. Silakan hubungi admin untuk memutuskan koneksi yang lama terlebih dahulu.',
            ];
        }

        // Skenario 3: Telegram ID sudah dipakai user lain -> konflik!
        $existingUser = User::where('telegram_id', $telegramId)
            ->where('id', '!=', $user->id)
            ->first();

        if ($existingUser) {
            Log::warning('Telegram linking conflict: Telegram ID already used by another user', [
                'target_user_id' => $user->id,
                'target_email' => $user->email,
                'existing_user_id' => $existingUser->id,
                'existing_email' => $existingUser->email,
                'telegram_id' => $telegramId,
            ]);

            return [
                'method'  => 'sendMessage',
                'chat_id' => $chatId,
                'text'    => 'Telegram ID ini sudah terhubung dengan akun lain (' . $existingUser->email . '). Jika ini akun kamu, silakan hubungi admin untuk memutuskan koneksi yang lama terlebih dahulu.',
            ];
        }

        // Skenario 4: Semua aman, bisa link
        $user->telegram_id = $telegramId;
        $user->save();

        Log::info('Telegram account linked successfully', [
            'user_id' => $user->id,
            'telegram_id' => $telegramId,
            'email' => $user->email,
        ]);

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
}


