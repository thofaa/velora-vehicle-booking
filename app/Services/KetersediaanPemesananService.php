<?php

namespace App\Services;

use App\Models\Pemesanan;
use Illuminate\Support\Carbon;

class KetersediaanPemesananService
{
    /**
     * Status pemesanan yang dianggap "aktif" dan menghalangi jadwal yang overlap.
     *
     * @var array<int, string>
     */
    public const STATUS_AKTIF = ['menunggu_persetujuan', 'disetujui'];

    /**
     * Periksa apakah kendaraan tersedia pada rentang tanggal yang diminta.
     */
    public function kendaraanTersedia(
        int $idKendaraan,
        Carbon $mulai,
        Carbon $selesai,
        ?int $abaikanPemesananId = null,
    ): bool {
        return ! Pemesanan::query()
            ->where('id_kendaraan', $idKendaraan)
            ->when($abaikanPemesananId, fn ($q) => $q->where('id', '!=', $abaikanPemesananId))
            ->whereIn('status', self::STATUS_AKTIF)
            ->where('tanggal_mulai', '<', $selesai)
            ->where('tanggal_selesai', '>', $mulai)
            ->exists();
    }

    /**
     * Periksa apakah driver tersedia pada rentang tanggal yang diminta.
     */
    public function driverTersedia(
        int $idDriver,
        Carbon $mulai,
        Carbon $selesai,
        ?int $abaikanPemesananId = null,
    ): bool {
        return ! Pemesanan::query()
            ->where('id_driver', $idDriver)
            ->when($abaikanPemesananId, fn ($q) => $q->where('id', '!=', $abaikanPemesananId))
            ->whereIn('status', self::STATUS_AKTIF)
            ->where('tanggal_mulai', '<', $selesai)
            ->where('tanggal_selesai', '>', $mulai)
            ->exists();
    }
}
