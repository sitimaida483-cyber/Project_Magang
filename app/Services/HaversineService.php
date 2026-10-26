<?php

namespace App\Services;

/**
 * HaversineService
 * Menghitung jarak geodesic antara dua titik koordinat GPS (derajat desimal)
 * menggunakan formula Haversine dengan presisi satuan meter.
 */
class HaversineService
{
    /** Jari-jari bumi rata-rata dalam satuan meter */
    private const EARTH_RADIUS_METERS = 6371000;

    /**
     * Hitung jarak antara dua koordinat GPS dalam satuan meter.
     *
     * @param float $latFrom Lintang posisi awal (anggota)
     * @param float $lonFrom Bujur posisi awal (anggota)
     * @param float $latTo   Lintang posisi tujuan (kantor)
     * @param float $lonTo   Bujur posisi tujuan (kantor)
     * @return float Jarak dalam meter
     */
    public static function hitungJarak(float $latFrom, float $lonFrom, float $latTo, float $lonTo): float
    {
        $latFromRad = deg2rad($latFrom);
        $lonFromRad = deg2rad($lonFrom);
        $latToRad   = deg2rad($latTo);
        $lonToRad   = deg2rad($lonTo);

        $latDelta = $latToRad - $latFromRad;
        $lonDelta = $lonToRad - $lonFromRad;

        $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) +
            cos($latFromRad) * cos($latToRad) * pow(sin($lonDelta / 2), 2)));

        return round($angle * self::EARTH_RADIUS_METERS, 2);
    }
}
