<?php

namespace App\Core;

/**
 * Monetization-aware UX assistant
 *
 * Mengimplementasikan aturan di dokumen monetization:
 * - Polite info
 * - Soft-block saat trial expired
 * - Panduan upgrade tanpa menyebut harga / metode pembayaran
 *
 * Catatan: class ini bekerja di sisi backend dan mengembalikan array
 * yang bentuknya 1:1 dengan JSON schema di dokumen.
 */
class MonetizationUxAgent
{
    /**
     * @param  array  $context
     *  - subscription_status: trial|active|expired|null
     *  - trial_days_remaining: int|null
     *  - last_intent: expense|income|debt|null
     *  - channel: telegram|whatsapp|app|null
     *
     * @return array{
     *   action: string,
     *   message: string,
     *   show_upgrade_cta: bool
     * }
     */
    public function decide(array $context): array
    {
        $status = $context['subscription_status'] ?? null;
        $daysRemaining = $context['trial_days_remaining'] ?? null;
        $lastIntent = $context['last_intent'] ?? null;

        // Default: allow tanpa pesan tambahan
        $result = [
            'action' => 'allow',
            'message' => '',
            'show_upgrade_cta' => false,
        ];

        // CASE 1 — TRIAL ACTIVE
        if ($status === 'trial') {
            if (is_int($daysRemaining) && $daysRemaining <= 2 && $daysRemaining >= 0) {
                return [
                    'action' => 'remind_trial',
                    'message' => "FYI, trial kamu tinggal {$daysRemaining} hari lagi ya 😊",
                    'show_upgrade_cta' => false,
                ];
            }

            return $result;
        }

        // CASE 2 & 3 — TRIAL EXPIRED (SOFT BLOCK)
        if ($status === 'expired') {
            $baseMessage = "Catatan kamu aman 👍\nTapi masa trial sudah berakhir.\n\nUntuk lanjut mencatat pengeluaran, kamu bisa upgrade kapan aja.";

            // User terus kirim pesan expense/income/debt setelah expired
            if (in_array($lastIntent, ['expense', 'income', 'debt'], true)) {
                $baseMessage .= "\n\nYuk upgrade dulu supaya bisa lanjut mencatat 😊";
            }

            return [
                'action' => 'soft_block',
                'message' => $baseMessage,
                'show_upgrade_cta' => true,
            ];
        }

        // CASE — ACTIVE atau tidak dikenal: allow saja.
        return $result;
    }
}


