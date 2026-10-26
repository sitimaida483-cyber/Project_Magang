<?php

namespace App\Services;

use App\Models\Notifikasi;
use App\Models\PerangkatNotifikasi;
use Minishlink\WebPush\Subscription;
use Minishlink\WebPush\WebPush;

/**
 * NotifikasiService
 * Mengelola pengiriman notifikasi Web Push (VAPID) ke perangkat anggota
 * serta mencatat riwayat pengiriman ke tabel `notifikasi`.
 */
class NotifikasiService
{
    /**
     * Kirim pesan notifikasi Web Push ke perangkat anggota tertentu.
     *
     * @param PerangkatNotifikasi $perangkat
     * @param string $jenisNotifikasi 'pengingat_masuk' | 'pengingat_pulang' | 'presensi_berhasil'
     * @param string $judul
     * @param string $pesan
     * @param string $targetUrl
     * @return bool Status pengiriman
     */
    public static function kirim(
        PerangkatNotifikasi $perangkat,
        string $jenisNotifikasi,
        string $judul,
        string $pesan,
        string $targetUrl = '/presensi'
    ): bool {
        $vapidConfig = config('presensi.vapid');

        if (empty($vapidConfig['public_key']) || empty($vapidConfig['private_key'])) {
            // Catat gagal karena konfigurasi VAPID belum lengkap
            self::catatLog($perangkat->id_perangkat, $jenisNotifikasi, $pesan, 'gagal');
            return false;
        }

        try {
            $subscriptionData = json_decode($perangkat->token_notifikasi, true);
            if (! $subscriptionData || ! isset($subscriptionData['endpoint'])) {
                self::catatLog($perangkat->id_perangkat, $jenisNotifikasi, $pesan, 'gagal');
                return false;
            }

            $subscription = Subscription::create([
                'endpoint'        => $subscriptionData['endpoint'],
                'publicKey'       => $subscriptionData['keys']['p256dh'] ?? null,
                'authToken'       => $subscriptionData['keys']['auth'] ?? null,
                'contentEncoding' => 'aesgcm',
            ]);

            $auth = [
                'VAPID' => [
                    'subject'    => $vapidConfig['subject'],
                    'publicKey'  => $vapidConfig['public_key'],
                    'privateKey' => $vapidConfig['private_key'],
                ],
            ];

            $webPush = new WebPush($auth);
            $webPush->setDefaultOptions([
                'TTL' => 300,
            ]);

            $payload = json_encode([
                'title' => $judul,
                'body'  => $pesan,
                'url'   => $targetUrl,
            ]);

            $report = $webPush->sendOneNotification($subscription, $payload);

            if ($report->isSuccess()) {
                self::catatLog($perangkat->id_perangkat, $jenisNotifikasi, $pesan, 'terkirim');
                return true;
            } else {
                // Jika endpoint kedaluwarsa atau tidak valid lagi (404/410), nonaktifkan perangkat
                if ($report->isSubscriptionExpired()) {
                    $perangkat->update(['status' => 'nonaktif']);
                }
                self::catatLog($perangkat->id_perangkat, $jenisNotifikasi, $pesan . ' (Error: ' . $report->getReason() . ')', 'gagal');
                return false;
            }
        } catch (\Throwable $e) {
            report($e);
            self::catatLog($perangkat->id_perangkat, $jenisNotifikasi, $pesan . ' (Ex: ' . $e->getMessage() . ')', 'gagal');
            return false;
        }
    }

    /**
     * Helper mencatat riwayat ke tabel `notifikasi`.
     */
    private static function catatLog(int $idPerangkat, string $jenisNotifikasi, string $pesan, string $status): void
    {
        Notifikasi::create([
            'id_perangkat'     => $idPerangkat,
            'jenis_notifikasi' => $jenisNotifikasi,
            'pesan'            => $pesan,
            'waktu_kirim'      => now(),
            'status_kirim'     => $status,
        ]);
    }
}
