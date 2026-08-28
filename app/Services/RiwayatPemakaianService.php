<?php

namespace App\Services;

use App\Models\Kendaraan;
use App\Models\Pemesanan;
use Illuminate\Support\Carbon;

class RiwayatPemakaianService
{
    /**
     * Riwayat pemakaian harian kendaraan selama satu tahun (dipakai / tidak).
     *
     * @return array{
     *     tahun: int,
     *     kendaraan: array<string, mixed>|null,
     *     hari: array<int, array{tanggal: string, dipakai: bool, id_pemesanan: int|null}>
     * }
     */
    public function data(int $kendaraanId, int $tahun): array
    {
        // Satu query tanpa N+1: semua pemesanan disetujui yang overlap dengan tahun tersebut.
        // Rentang setengah-terbuka agar tanggal batas tidak terpotong di SQLite.
        $pemesanan = Pemesanan::query()
            ->where('id_kendaraan', $kendaraanId)
            ->where('status', Pemesanan::STATUS_DISETUJUI)
            ->where('tanggal_mulai', '<', ($tahun + 1).'-01-01')
            ->where('tanggal_selesai', '>=', "$tahun-01-01")
            ->get(['id', 'tanggal_mulai', 'tanggal_selesai']);

        $kendaraan = Kendaraan::find($kendaraanId, ['id', 'nomor_polisi', 'merk', 'tipe']);

        $hari = [];
        $awal = Carbon::create($tahun, 1, 1);

        for ($tanggal = $awal->copy(); $tanggal->year === $tahun; $tanggal->addDay()) {
            $booking = $pemesanan->first(
                fn (Pemesanan $p) => $p->tanggal_mulai->lte($tanggal) && $p->tanggal_selesai->gte($tanggal)
            );

            $hari[] = [
                'tanggal' => $tanggal->toDateString(),
                'dipakai' => $booking !== null,
                'id_pemesanan' => $booking?->id,
            ];
        }

        return [
            'tahun' => $tahun,
            'kendaraan' => $kendaraan ? [
                'id' => $kendaraan->id,
                'nomor_polisi' => $kendaraan->nomor_polisi,
                'merk' => $kendaraan->merk,
                'tipe' => $kendaraan->tipe,
            ] : null,
            'hari' => $hari,
        ];
    }
}
