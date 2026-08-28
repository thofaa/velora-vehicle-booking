<?php

namespace App\Services;

use App\Models\Pemesanan;
use Illuminate\Support\Carbon;

class PemesananExportService
{
    /**
     * Label status pemesanan untuk laporan.
     *
     * @var array<string, string>
     */
    private const STATUS_LABEL = [
        Pemesanan::STATUS_MENUNGGU => 'Menunggu Persetujuan',
        Pemesanan::STATUS_DISETUJUI => 'Disetujui',
        Pemesanan::STATUS_DITOLAK => 'Ditolak',
        Pemesanan::STATUS_DIBATALKAN => 'Dibatalkan',
    ];

    /**
     * Baris header laporan.
     *
     * @return array<int, string>
     */
    public function header(): array
    {
        return [
            'ID',
            'ID Driver',
            'Driver',
            'ID Kendaraan',
            'Kendaraan',
            'Merk',
            'Tipe',
            'ID Admin',
            'Admin',
            'Tanggal Mulai',
            'Tanggal Selesai',
            'Status',
            'Dibuat Pada',
        ];
    }

    /**
     * Baris laporan pemesanan pada rentang tanggal mulai yang diberikan.
     * ID driver, kendaraan, dan admin disertai nama deskriptifnya.
     * Rentang setengah-terbuka [dari, hingga+1hari) agar tanggal batas tidak terpotong
     * (SQLite menyimpan kolom date sebagai datetime).
     *
     * @return array<int, array<int, string|int>>
     */
    public function data(Carbon $dari, Carbon $hingga): array
    {
        $rows = Pemesanan::query()
            ->with(['kendaraan', 'driver', 'admin'])
            ->where('tanggal_mulai', '>=', $dari->startOfDay())
            ->where('tanggal_mulai', '<', $hingga->copy()->addDay()->startOfDay())
            ->orderBy('tanggal_mulai')
            ->get();

        return $rows->map(fn (Pemesanan $p): array => [
            $p->id,
            $p->id_driver,
            $p->driver?->nama ?? '-',
            $p->id_kendaraan,
            $p->kendaraan?->nomor_polisi ?? '-',
            $p->kendaraan?->merk ?? '-',
            $p->kendaraan?->tipe ?? '-',
            $p->id_admin,
            $p->admin?->name ?? '-',
            $p->tanggal_mulai?->format('d/m/Y') ?? '-',
            $p->tanggal_selesai?->format('d/m/Y') ?? '-',
            self::STATUS_LABEL[$p->status] ?? $p->status,
            $p->created_at?->format('d/m/Y H:i') ?? '-',
        ])->all();
    }
}
