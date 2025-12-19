<?php

namespace App\Services;

use App\Models\Transaction;
use App\Models\User;
use Carbon\Carbon;

/**
 * ReceiptPipeline
 *
 * Mengubah hasil parsed_text dari OpenAI (struk) menjadi
 * struktur internal + pesan konfirmasi, dan menyimpan transaksi
 * setelah user konfirmasi.
 *
 * Catatan: kita anggap parsed_text berupa JSON string dengan minimal:
 * - merchant_name: string|null
 * - transaction_date: string|null (YYYY-MM-DD atau format umum)
 * - total_amount: int|float|string|null
 */
class ReceiptPipeline
{
    /**
     * Normalisasi hasil parsed_text menjadi array internal + pesan konfirmasi.
     *
     * @param  User   $user
     * @param  string $parsedText JSON string dari OpenAI
     * @return array{
     *   ok: bool,
     *   error?: string,
     *   preview?: array,
     *   confirmation_message?: string
     * }
     */
    public function prepareFromParsedText(User $user, string $parsedText): array
    {
        $data = json_decode($parsedText, true);

        if (! is_array($data)) {
            return [
                'ok' => false,
                'error' => 'Format jawaban struk tidak valid.',
            ];
        }

        $merchant = $data['merchant_name'] ?? null;
        $dateRaw = $data['transaction_date'] ?? null;
        $totalRaw = $data['total_amount'] ?? null;

        $amount = $this->normalizeAmount($totalRaw);
        $date = $this->normalizeDate($dateRaw);

        if ($amount === null) {
            return [
                'ok' => false,
                'error' => 'Nominal total di struk belum jelas.',
            ];
        }

        $transactedAt = $date ?? Carbon::now();
        $description = $merchant ? "Belanja di {$merchant}" : 'Belanja dari struk';

        $preview = [
            'user_id' => $user->id,
            'type' => 'expense',
            'amount' => $amount,
            'description' => $description,
            'transacted_at' => $transactedAt->toDateTimeString(),
        ];

        $messageLines = [];
        $messageLines[] = 'Aku baca struk kamu begini:';
        $messageLines[] = sprintf('Total: Rp%s', number_format($amount, 0, ',', '.'));

        if ($merchant) {
            $messageLines[] = "Toko: {$merchant}";
        }

        if ($date) {
            $messageLines[] = 'Tanggal: ' . $date->toDateString();
        }

        $messageLines[] = '';
        $messageLines[] = 'Simpan sebagai pengeluaran?';
        $messageLines[] = 'Balas:';
        $messageLines[] = 'YA    → simpan';
        $messageLines[] = 'TIDAK → batal';

        return [
            'ok' => true,
            'preview' => $preview,
            'confirmation_message' => implode("\n", $messageLines),
        ];
    }

    /**
     * Simpan transaksi setelah user konfirmasi.
     *
     * @param  array $preview Hasil dari prepareFromParsedText()['preview']
     * @return Transaction
     */
    public function storeFromPreview(array $preview): Transaction
    {
        // Pastikan hanya field yang diizinkan yang terpakai.
        $payload = [
            'user_id' => $preview['user_id'],
            'type' => $preview['type'],
            'amount' => $preview['amount'],
            'description' => $preview['description'],
            'transacted_at' => $preview['transacted_at'],
        ];

        return Transaction::create($payload);
    }

    protected function normalizeAmount(mixed $raw): ?int
    {
        if ($raw === null) {
            return null;
        }

        if (is_numeric($raw)) {
            return (int) round((float) $raw);
        }

        if (is_string($raw)) {
            $clean = preg_replace('/[^\d]/', '', $raw);

            return $clean !== '' ? (int) $clean : null;
        }

        return null;
    }

    protected function normalizeDate(mixed $raw): ?Carbon
    {
        if (! is_string($raw) || trim($raw) === '') {
            return null;
        }

        try {
            return Carbon::parse($raw);
        } catch (\Throwable $e) {
            return null;
        }
    }
}


