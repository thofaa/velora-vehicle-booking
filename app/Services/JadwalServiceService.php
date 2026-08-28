<?php

namespace App\Services;

use App\Models\JadwalService;
use App\Models\Kendaraan;
use Illuminate\Support\Carbon;

class JadwalServiceService
{
    /**
     * Jadwal service satu kendaraan pada satu bulan, dengan status terlewat dihitung.
     *
     * @return array{
     *     id_kendaraan: int,
     *     bulan: int,
     *     tahun: int,
     *     hari_ini: string,
     *     kendaraan: array<string, mixed>|null,
     *     jadwal: array<int, array<string, mixed>>
     * }
     */
    public function data(int $kendaraanId, int $bulan, int $tahun): array
    {
        $mulai = Carbon::create($tahun, $bulan, 1)->startOfMonth();

        $kendaraan = Kendaraan::find($kendaraanId, ['id', 'nomor_polisi', 'merk', 'tipe']);

        $jadwal = JadwalService::query()
            ->where('id_kendaraan', $kendaraanId)
            ->where('tanggal_service', '>=', $mulai)
            ->where('tanggal_service', '<', $mulai->copy()->addMonth())
            ->orderBy('tanggal_service')
            ->get(['id_jadwal_service', 'tanggal_service', 'jenis_service', 'status'])
            ->map(function (JadwalService $service) {
                $status = $service->status;

                if ($status === JadwalService::STATUS_TERJADWAL && $service->tanggal_service->lt(today())) {
                    $status = JadwalService::STATUS_TERLEWAT;
                }

                return [
                    'id' => $service->id_jadwal_service,
                    'tanggal' => $service->tanggal_service->toDateString(),
                    'jenis' => $service->jenis_service,
                    'status' => $status,
                ];
            })
            ->values();

        return [
            'id_kendaraan' => $kendaraanId,
            'bulan' => $bulan,
            'tahun' => $tahun,
            'hari_ini' => today()->toDateString(),
            'kendaraan' => $kendaraan ? [
                'id' => $kendaraan->id,
                'nomor_polisi' => $kendaraan->nomor_polisi,
                'merk' => $kendaraan->merk,
                'tipe' => $kendaraan->tipe,
            ] : null,
            'jadwal' => $jadwal->all(),
        ];
    }
}
