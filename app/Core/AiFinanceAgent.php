<?php

namespace App\Core;

use Illuminate\Support\Str;

/**
 * AiFinanceAgent
 *
 * Implementasi backend dari spesifikasi bisnis di `bisnis.md`.
 * Saat ini masih rule-based (belum panggil LLM),
 * tapi output-nya sudah mengikuti schema JSON yang didefinisikan.
 *
 * Nanti bisa diganti untuk memanggil LLM via API key,
 * dengan tetap mempertahankan interface dan bentuk output yang sama.
 */
class AiFinanceAgent
{
    /**
     * Analisa input teks dari user dan kembalikan array sesuai schema JSON di bisnis.md.
     *
     * @param  string  $input           Teks original dari user.
     * @param  array   $backendContext  Metadata dari backend (mis. subscription_context).
     * @return array
     */
    public function analyze(string $input, array $backendContext = []): array
    {
        $inputType = 'text';
        $normalized = mb_strtolower(trim($input));

        // STEP 2 — Intent classification
        $intent = $this->classifyIntent($normalized);

        // STEP 3 — Amount normalization
        $amountResult = $this->extractAmount($normalized);

        // STEP 4 — Data extraction (sederhana, nanti bisa di-improve)
        $data = $this->extractDataByIntent($intent, $input);

        $data['amount'] = $amountResult['amount'];

        // Confidence & safety dasar
        $confidence = $amountResult['confidence'] ?? 'medium';
        $amountAmbiguous = $amountResult['amount_ambiguous'];

        $isSafe = $this->checkSafety($intent, $amountResult['amount'], $amountAmbiguous);

        // STEP 7 — Subscription awareness (context dari backend)
        $subscriptionContext = $backendContext['subscription_context'] ?? [
            'status' => null,
            'trial_days_remaining' => null,
        ];

        // STEP 8 — UX confirmation message (tanpa emoji & markdown)
        $uxMessage = $this->buildUxMessage($intent, $data, $amountResult, $subscriptionContext);

        return [
            'input_type' => $inputType,
            'intent' => $intent,
            'data' => $data + [
                'merchant_name' => $data['merchant_name'] ?? '',
                'transaction_date' => $data['transaction_date'] ?? '',
            ],
            'confidence' => $confidence,
            'amount_ambiguous' => $amountAmbiguous,
            'needs_confirmation' => true,
            'is_safe' => $isSafe,
            'subscription_context' => [
                'status' => $subscriptionContext['status'] ?? null,
                'trial_days_remaining' => $subscriptionContext['trial_days_remaining'] ?? null,
            ],
            'ux_message' => $uxMessage,
        ];
    }

    protected function classifyIntent(string $text): string
    {
        if ($this->looksLikeConfirmation($text)) {
            return 'confirm';
        }

        if (Str::contains($text, ['keluar', 'pengeluaran', 'bayar', 'beli'])) {
            return 'expense';
        }

        if (Str::contains($text, ['masuk', 'pemasukan', 'gaji', 'income'])) {
            return 'income';
        }

        if (Str::contains($text, ['utang', 'piutang', 'ngutang', 'hutang'])) {
            return 'debt';
        }

        return 'unknown';
    }

    protected function looksLikeConfirmation(string $text): bool
    {
        $confirmWords = ['ya', 'y', 'ok', 'oke', 'lanjut'];
        $cancelWords = ['tidak', 'ga', 'gak', 'batal', 'cancel'];

        return in_array($text, $confirmWords, true) || in_array($text, $cancelWords, true);
    }

    /**
     * Normalisasi angka dengan gaya Indonesia (k, rb, jt).
     *
     * @return array{amount: ?int, amount_ambiguous: bool, confidence: string}
     */
    protected function extractAmount(string $text): array
    {
        $pattern = '/(\d+(?:[.,]\d{3})*|\d+)\s*(k|rb|ribu|jt|juta)?/iu';

        if (! preg_match($pattern, $text, $matches)) {
            return [
                'amount' => null,
                'amount_ambiguous' => false,
                'confidence' => 'medium',
            ];
        }

        $numberRaw = $matches[1] ?? '';
        $multiplierRaw = mb_strtolower($matches[2] ?? '');

        // Hilangkan pemisah ribuan
        $number = (int) str_replace([',', '.'], '', $numberRaw);
        $multiplier = 1;

        if (in_array($multiplierRaw, ['k', 'rb', 'ribu'], true)) {
            $multiplier = 1000;
        } elseif (in_array($multiplierRaw, ['jt', 'juta'], true)) {
            $multiplier = 1000000;
        }

        $finalAmount = $number * $multiplier;

        // Aturan ambiguity: jumlah < 1000 tanpa multiplier
        if ($multiplier === 1 && $number < 1000) {
            return [
                'amount' => null,
                'amount_ambiguous' => true,
                'confidence' => 'low',
            ];
        }

        return [
            'amount' => $finalAmount,
            'amount_ambiguous' => false,
            'confidence' => $multiplier === 1 ? 'medium' : 'high',
        ];
    }

    /**
     * Ekstraksi field dasar berdasarkan intent.
     */
    protected function extractDataByIntent(string $intent, string $originalText): array
    {
        $today = now()->toDateString();

        if ($intent === 'expense') {
            return [
                'description' => $originalText,
                'source' => '',
                'name' => '',
                'date' => $today,
                'due_date' => '',
            ];
        }

        if ($intent === 'income') {
            return [
                'description' => '',
                'source' => $originalText,
                'name' => '',
                'date' => $today,
                'due_date' => '',
            ];
        }

        if ($intent === 'debt') {
            return [
                'description' => '',
                'source' => '',
                'name' => $originalText,
                'date' => $today,
                'due_date' => '',
            ];
        }

        return [
            'description' => $originalText,
            'source' => '',
            'name' => '',
            'date' => $today,
            'due_date' => '',
        ];
    }

    protected function checkSafety(string $intent, ?int $amount, bool $amountAmbiguous): bool
    {
        if ($amountAmbiguous) {
            return false;
        }

        if ($amount === null) {
            return true;
        }

        if ($intent === 'expense' && $amount > 100000000) {
            return false;
        }

        if ($intent === 'income' && $amount > 1000000000) {
            return false;
        }

        return true;
    }

    protected function buildUxMessage(string $intent, array $data, array $amountResult, array $subscriptionContext): string
    {
        $lines = [];
        $lines[] = 'Aku tangkap begini ya:';
        $summary = '';

        if ($intent === 'expense') {
            $summary = sprintf(
                'Pengeluaran %s pada %s.',
                $this->formatAmountForHuman($amountResult['amount']),
                $data['date'] ?? ''
            );
        } elseif ($intent === 'income') {
            $summary = sprintf(
                'Pemasukan %s pada %s.',
                $this->formatAmountForHuman($amountResult['amount']),
                $data['date'] ?? ''
            );
        } elseif ($intent === 'debt') {
            $summary = sprintf(
                'Utang dengan catatan: %s.',
                $data['name'] ?: $data['description']
            );
        } elseif ($intent === 'confirm') {
            $summary = 'Ini terlihat seperti konfirmasi (YA/TIDAK/BATAL).';
        } else {
            $summary = 'Aku belum yakin ini jenis transaksi apa.';
        }

        $lines[] = $summary;

        if ($amountResult['amount_ambiguous']) {
            $lines[] = 'Nominal belum jelas. Misalnya, maksudnya 20000 atau 200?';
        }

        $lines[] = '';
        $lines[] = 'Balas:';
        $lines[] = 'YA    → simpan';
        $lines[] = 'TIDAK → batal';

        return implode("\n", $lines);
    }

    protected function formatAmountForHuman(?int $amount): string
    {
        if ($amount === null) {
            return '(nominal belum jelas)';
        }

        return 'Rp' . number_format($amount, 0, ',', '.');
    }
}


