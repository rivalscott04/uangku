<?php

namespace App\Core;

use App\Models\User;
use App\Models\Subscription;
use Carbon\Carbon;

/**
 * SubscriptionGuard
 *
 * Mengelola aturan trial dan subscription:
 * - Trial 7 hari sejak user pertama kali terdaftar.
 * - Setelah trial, harus punya subscription aktif.
 *
 * Implementasi detail (tabel subscriptions, dsb) akan diisi setelah
 * model & migrasi dibuat, tapi interface dasar sudah disiapkan.
 */
class SubscriptionGuard
{
    /**
     * Menghasilkan context subscription untuk AI / backend,
     * sesuai dengan spesifikasi di bisnis.md.
     *
     * @return array{status: string, trial_days_remaining: ?int}
     */
    public function getContext(User $user): array
    {
        // Jika user ditandai sebagai billing_exempt (owner/internal),
        // selalu anggap statusnya aktif dan tidak terikat trial/subscription.
        if (property_exists($user, 'billing_exempt') && $user->billing_exempt) {
            return [
                'status' => 'active',
                'trial_days_remaining' => null,
            ];
        }

        $createdAt = $user->created_at ? Carbon::parse($user->created_at) : Carbon::now();
        $trialEndsAt = $createdAt->copy()->addDays(7);

        if (now()->lessThanOrEqualTo($trialEndsAt)) {
            $remainingDays = now()->diffInDays($trialEndsAt, false) + 1;

            return [
                'status' => 'trial',
                'trial_days_remaining' => $remainingDays,
            ];
        }

        /** @var Subscription|null $active */
        $active = $user->subscriptions()
            ->whereNotNull('approved_at')
            ->where('starts_at', '<=', now())
            ->where('ends_at', '>=', now())
            ->latest('ends_at')
            ->first();

        if ($active && $active->isActive()) {
            return [
                'status' => 'active',
                'trial_days_remaining' => null,
            ];
        }

        return [
            'status' => 'expired',
            'trial_days_remaining' => null,
        ];
    }

    /**
     * Cek apakah user boleh mengakses fitur utama.
     *
     * @param User $user
     * @return array{allowed: bool, message: string}
     */
    public function checkAccess(User $user): array
    {
        $context = $this->getContext($user);

        if ($context['status'] === 'trial') {
            return [
                'allowed' => true,
                'message' => "Trial aktif. Sisa sekitar {$context['trial_days_remaining']} hari.",
            ];
        }

        if ($context['status'] === 'expired') {
            return [
                'allowed' => false,
                'message' => 'Masa trial kamu sudah habis. Silakan upgrade untuk lanjut pakai fitur catat keuangan 🙏',
            ];
        }

        return [
            'allowed' => true,
            'message' => 'Subscription aktif.',
        ];
    }
}


