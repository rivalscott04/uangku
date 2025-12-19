<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;

class SetTelegramWebhook extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'telegram:set-webhook 
                            {--url= : Custom webhook URL (optional, will use APP_URL if not provided)}
                            {--remove : Remove webhook instead of setting it}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Set or remove Telegram webhook URL dynamically based on APP_URL or custom URL';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $botToken = Config::get('services.telegram.bot_token');
        $apiBase = rtrim(Config::get('services.telegram.api_url', 'https://api.telegram.org'), '/');

        if (empty($botToken)) {
            $this->error('TELEGRAM_BOT_TOKEN tidak ditemukan di .env');
            return Command::FAILURE;
        }

        // Jika --remove, hapus webhook
        if ($this->option('remove')) {
            return $this->removeWebhook($apiBase, $botToken);
        }

        // Ambil URL webhook (custom atau dari APP_URL)
        $customUrl = $this->option('url');
        $webhookUrl = $customUrl 
            ?: Config::get('services.telegram.webhook_url')
            ?: Config::get('app.url');

        if (empty($webhookUrl)) {
            $this->error('APP_URL tidak ditemukan di .env. Gunakan --url untuk set custom URL.');
            return Command::FAILURE;
        }

        // Pastikan URL sudah lengkap dengan path webhook
        $webhookUrl = rtrim($webhookUrl, '/') . '/webhook/telegram';

        // Validasi URL harus HTTPS (kecuali localhost)
        if (!str_starts_with($webhookUrl, 'https://') && !str_contains($webhookUrl, 'localhost') && !str_contains($webhookUrl, '127.0.0.1')) {
            $this->warn('Peringatan: Telegram webhook harus menggunakan HTTPS (kecuali localhost).');
            $this->warn('Untuk development, gunakan tunneling seperti ngrok: ngrok http 8000');
        }

        $this->info("Mengatur webhook ke: {$webhookUrl}");

        try {
            $response = Http::post("{$apiBase}/bot{$botToken}/setWebhook", [
                'url' => $webhookUrl,
            ]);

            $result = $response->json();

            if ($result['ok'] ?? false) {
                $this->info('✓ Webhook berhasil diatur!');
                
                if (isset($result['result']['url'])) {
                    $this->line("  URL: {$result['result']['url']}");
                }
                if (isset($result['result']['pending_update_count'])) {
                    $this->line("  Pending updates: {$result['result']['pending_update_count']}");
                }

                return Command::SUCCESS;
            } else {
                $this->error('Gagal mengatur webhook: ' . ($result['description'] ?? 'Unknown error'));
                return Command::FAILURE;
            }
        } catch (\Exception $e) {
            $this->error('Error: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }

    /**
     * Remove webhook dari Telegram.
     */
    private function removeWebhook(string $apiBase, string $botToken): int
    {
        $this->info('Menghapus webhook...');

        try {
            $response = Http::post("{$apiBase}/bot{$botToken}/deleteWebhook");

            $result = $response->json();

            if ($result['ok'] ?? false) {
                $this->info('✓ Webhook berhasil dihapus!');
                return Command::SUCCESS;
            } else {
                $this->error('Gagal menghapus webhook: ' . ($result['description'] ?? 'Unknown error'));
                return Command::FAILURE;
            }
        } catch (\Exception $e) {
            $this->error('Error: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}

