<?php

namespace App\Core;

/**
 * AI / Rule-based Intent Parser
 *
 * Untuk tahap awal, parser ini masih sangat sederhana (regex/keyword).
 * Nanti bisa diganti dengan model AI yang lebih kompleks tanpa mengubah pemanggilnya.
 */
class IntentParser
{
    /**
     * Parse pesan teks dan kembalikan intent sederhana.
     *
     * Contoh format natural language yang ingin didukung ke depan:
     * - "keluar 50k makan siang"
     * - "masuk 1.000.000 gaji"
     * - "utang 200k ke budi"
     *
     * @param string $text
     * @return array
     */
    public function parse(string $text): array
    {
        $normalized = mb_strtolower(trim($text));

        $intent = 'unknown';

        if (str_contains($normalized, 'keluar') || str_contains($normalized, 'pengeluaran')) {
            $intent = 'expense';
        } elseif (str_contains($normalized, 'masuk') || str_contains($normalized, 'pemasukan')) {
            $intent = 'income';
        } elseif (str_contains($normalized, 'utang') || str_contains($normalized, 'piutang')) {
            $intent = 'debt';
        }

        return [
            'intent'        => $intent,
            'raw'           => $text,
            'normalized'    => $normalized,
            // Sementara, untuk bantu debug response di channel
            'debug_message' => "Intent terdeteksi: {$intent}",
        ];
    }
}


