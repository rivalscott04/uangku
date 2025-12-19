<?php

namespace App\Core;

use App\Core\IntentParser;
use App\Core\SubscriptionGuard;
use App\Core\AiFinanceAgent;
use App\Models\User;

/**
 * Core Message Engine
 *
 * Tanggung jawab:
 * - Menerima pesan yang sudah dinormalisasi dari channel adapter.
 * - Cek subscription/trial user.
 * - Parse intent pesan.
 * - Mengarahkan ke handler yang tepat (pencatatan transaksi, dsb).
 *
 * Untuk tahap awal, implementasi dibuat sederhana (rule-based),
 * dan nanti bisa diekspansi dengan AI yang lebih pintar.
 */
class MessageProcessor
{
    public function __construct(
        protected IntentParser $intentParser,
        protected SubscriptionGuard $subscriptionGuard,
        protected AiFinanceAgent $aiFinanceAgent,
    ) {
    }

    /**
     * Proses pesan teks dari user.
     *
     * @param User  $user   User yang mengirim pesan.
     * @param string $text  Pesan teks yang sudah dinormalisasi.
     * @param array $context Informasi tambahan dari channel (chat_id, dsb).
     *
     * @return array Payload respon generik yang nanti akan diterjemahkan oleh channel adapter.
     */
    public function process(User $user, string $text, array $context = []): array
    {
        // Cek trial / subscription dulu
        $check = $this->subscriptionGuard->checkAccess($user);
        if (! $check['allowed']) {
            return [
                'type'    => 'text',
                'message' => $check['message'],
            ];
        }

        // Subscription context untuk AI
        $subscriptionContext = $this->subscriptionGuard->getContext($user);

        // Analisa menggunakan AiFinanceAgent sesuai spesifikasi bisnis.md
        $aiResult = $this->aiFinanceAgent->analyze($text, [
            'subscription_context' => $subscriptionContext,
            'backend_context' => $context,
        ]);

        return [
            'type'    => 'text',
            'message' => $aiResult['ux_message'] ?? 'Pesan kamu sudah diterima.',
            'meta'    => $aiResult,
        ];
    }
}


