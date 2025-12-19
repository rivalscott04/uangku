<?php

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Config;

Route::get('/', function () {
    return view('welcome');
});

// Redirect GET /register ke landing + modal signup, supaya tidak error MethodNotAllowed.
Route::get('/register', function () {
    return redirect('/#signup-modal');
});

// Simple registration endpoint for 7-day trial landing CTA (POST)
Route::post('/register', function (Request $request) {
    $validated = $request->validate([
        'name' => ['required', 'string', 'max:255'],
        'email' => ['required', 'string', 'email', 'max:255', 'unique:' . User::class],
        'password' => ['required', 'confirmed', 'min:8'],
        // Pilihan channel komunikasi utama user
        'preferred_channel' => ['required', 'in:whatsapp,telegram'],
    ]);

    $user = User::create([
        'name' => $validated['name'],
        'email' => $validated['email'],
        'preferred_channel' => $validated['preferred_channel'],
        'password' => Hash::make($validated['password']),
    ]);

    Auth::login($user);

    // Jika user pilih Telegram, arahkan ke deep-link bot untuk linking akun.
    if ($validated['preferred_channel'] === 'telegram') {
        $botUsername = (string) Config::get('services.telegram.bot_username', '');
        if ($botUsername !== '') {
            // Token aman yang meng-encode user id, akan dibaca saat /start <token> di Telegram.
            $token = urlencode(Crypt::encryptString((string) $user->id));
            $deepLink = "https://t.me/{$botUsername}?start={$token}";

            // Tampilkan halaman konfirmasi yang rapi tanpa auto-redirect ke telegram.org.
            $html = <<<HTML
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Menghubungkan ke Telegram</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        body { font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif; background: #f9fafb; color: #111827; display: flex; align-items: center; justify-content: center; min-height: 100vh; margin: 0; }
        .card { background: white; padding: 2.25rem 2.75rem; border-radius: 1rem; box-shadow: 0 10px 40px rgba(15, 23, 42, 0.08); max-width: 420px; width: 100%; text-align: center; }
        .icon-wrapper { width: 60px; height: 60px; border-radius: 999px; background: #22c55e1a; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.25rem; }
        .check-icon { width: 36px; height: 36px; color: #22c55e; animation: pop 500ms ease-out forwards; transform-origin: center; }
        @keyframes pop {
            0% { transform: scale(0.4); opacity: 0; }
            60% { transform: scale(1.1); opacity: 1; }
            100% { transform: scale(1); opacity: 1; }
        }
        .title { font-size: 1.25rem; font-weight: 700; margin-bottom: 0.5rem; }
        .subtitle { font-size: 0.95rem; color: #6b7280; margin-bottom: 1.5rem; line-height: 1.6; }
        .btn { display: inline-flex; align-items: center; justify-content: center; padding: 0.7rem 1.4rem; border-radius: 999px; background: #4B3EE4; color: white; font-weight: 600; font-size: 0.9rem; text-decoration: none; margin-top: 0.5rem; }
        .btn:hover { background: #3b31b7; }
        .small { font-size: 0.75rem; color: #9ca3af; margin-top: 1rem; }
    </style>
</head>
<body>
    <div class="card">
        <div class="icon-wrapper">
            <svg class="check-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                <circle cx="12" cy="12" r="10" stroke-width="2" class="text-green-500" />
                <path d="M7 12.5L10.2 15.5L17 9" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
        </div>
        <div class="title">Pendaftaran berhasil</div>
        <div class="subtitle">
            Akun kamu sudah aktif dan trial 7 hari dimulai.<br>
            Klik tombol di bawah untuk membuka bot Telegram Uangku dan menyambungkan akunmu.
        </div>
        <a href="{$deepLink}" class="btn">
            Buka Telegram sekarang
        </a>
        <div class="small">
            Jika tidak otomatis terbuka, pastikan aplikasi Telegram sudah terpasang lalu coba klik lagi.
        </div>
    </div>
</body>
</html>
HTML;

            return response($html);
        }
    }

    return redirect('/')
        ->with('status', 'Terima kasih! Akun kamu sudah aktif, kamu bisa mulai mencoba Uangku selama 7 hari.');
})->name('register');

// Webhook Telegram (MVP)
Route::post('/webhook/telegram', \App\Http\Controllers\TelegramWebhookController::class)
    ->name('webhook.telegram');

// Auth Routes
Route::get('/login', [\App\Http\Controllers\Auth\LoginController::class, 'create'])->name('login');
Route::post('/login', [\App\Http\Controllers\Auth\LoginController::class, 'store']);
Route::post('/logout', [\App\Http\Controllers\Auth\LoginController::class, 'destroy'])->name('logout');

// Admin Routes
Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('/admin/dashboard', [\App\Http\Controllers\AdminController::class, 'index'])->name('admin.dashboard');
    Route::post('/admin/users/{user}/toggle-billing-exempt', [\App\Http\Controllers\AdminController::class, 'toggleBillingExempt'])->name('admin.users.toggleBillingExempt');
});
