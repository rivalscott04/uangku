<?php

namespace App\Http\Controllers;

use App\Services\OpenAiClient;
use App\Services\ReceiptPipeline;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Storage;

class ReceiptController extends Controller
{
    public function __construct(
        protected OpenAiClient $openAi,
        protected ReceiptPipeline $pipeline,
    ) {
    }

    /**
     * Terima upload gambar struk, simpan sementara di storage publik,
     * lalu kirim URL-nya ke OpenAI untuk dianalisis.
     *
     * Request:
     * - image: file (jpg/png/webp, max ~5MB)
     * - user_id: optional, ID user yang akan dikaitkan dengan transaksi
     *
     * Response JSON (contoh, jika user_id valid):
     * {
     *   "url": "https://app.test/storage/receipts/xxx.png",
     *   "openai_raw": {...},
     *   "parsed_text": "{ \"merchant_name\": \"Alfamart\", ... }",
     *   "preview": { ... },
     *   "confirmation_message": "Aku baca struk kamu begini: ...",
     *   "confirm_token": "...."
     * }
     */
    public function parse(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'image' => ['required', 'file', 'image', 'max:5120'],
            'user_id' => ['nullable', 'integer', 'exists:users,id'],
        ]);

        $file = $validated['image'];

        // Simpan ke disk 'public' supaya bisa diakses oleh OpenAI.
        $path = $file->store('receipts', 'public');
        $url = Storage::disk('public')->url($path);

        $result = $this->openAi->analyzeReceiptImage($url, [
            'channel' => 'api',
        ]);

        $response = [
            'url' => $url,
            'openai_raw' => $result['raw'] ?? null,
            'parsed_text' => $result['parsed_text'] ?? null,
        ];

        // Jika ada user_id dan parsed_text valid, siapkan preview + pesan konfirmasi.
        if (! empty($validated['user_id']) && ! empty($result['parsed_text'])) {
            $user = User::find($validated['user_id']);

            if ($user) {
                $pipelineResult = $this->pipeline->prepareFromParsedText($user, $result['parsed_text']);

                if ($pipelineResult['ok'] ?? false) {
                    $preview = $pipelineResult['preview'] ?? [];
                    $tokenPayload = json_encode($preview);

                    $response['preview'] = $preview;
                    $response['confirmation_message'] = $pipelineResult['confirmation_message'] ?? null;
                    $response['confirm_token'] = Crypt::encryptString($tokenPayload);
                } else {
                    $response['pipeline_error'] = $pipelineResult['error'] ?? 'Gagal memproses struk.';
                }
            }
        }

        return response()->json($response);
    }

    /**
     * Konfirmasi penyimpanan transaksi dari preview struk.
     *
     * Request:
     * - token: string (hasil dari field "confirm_token" endpoint parse)
     * - answer: string (ya|tidak)
     */
    public function confirm(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'token' => ['required', 'string'],
            'answer' => ['required', 'string', 'in:ya,tidak'],
        ]);

        if ($validated['answer'] === 'tidak') {
            return response()->json([
                'status' => 'cancelled',
                'message' => 'Penyimpanan transaksi dari struk dibatalkan.',
            ]);
        }

        try {
            $json = Crypt::decryptString($validated['token']);
            $preview = json_decode($json, true);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Token konfirmasi tidak valid.',
            ], 422);
        }

        if (! is_array($preview) || empty($preview['user_id'])) {
            return response()->json([
                'status' => 'error',
                'message' => 'Data transaksi tidak lengkap.',
            ], 422);
        }

        $transaction = $this->pipeline->storeFromPreview($preview);

        return response()->json([
            'status' => 'saved',
            'transaction' => $transaction,
        ]);
    }
}


