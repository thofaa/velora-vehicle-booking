<?php

namespace App\Services;

use App\Models\Kendaraan;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class KonsumsiBbmService
{
    /**
     * Nama bucket bulanan.
     *
     * @var array<int, string>
     */
    private const BULAN = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];

    /**
     * Total konsumsi BBM per bucket waktu dan per kendaraan, siap konsumsi Recharts.
     *
     * Bucket mingguan pakai aturan konsisten: minggu ke-N dihitung dari tanggal,
     * week = ceil(day_of_month / 7) sehingga 1-7 = minggu 1, 8-14 = minggu 2, dst.
     *
     * @param  array<int, int>  $kendaraanIds
     * @return array{
     *     kategori: string,
     *     tahun: int,
     *     bulan: int|null,
     *     kendaraan: array<int, array<string, mixed>>,
     *     data: array<int, array<string, mixed>>
     * }
     */
    public function data(array $kendaraanIds, string $kategori, int $tahun, ?int $bulan): array
    {
        $rentang = $this->rentang($kategori, $tahun, $bulan);

        // Agregasi SUM dilakukan di database, per (kendaraan, tanggal); pem-bucket-an
        // hanya mengelompokkan baris agregat yang kecil (maks. jumlah hari dalam rentang).
        // Rentang setengah-terbuka [mulai, selesai) agar tanggal batas tidak terpotong
        // (SQLite menyimpan kolom date sebagai datetime).
        $rows = DB::table('konsumsi_bbm')
            ->selectRaw('id_kendaraan, tanggal, SUM(jumlah_liter) as total')
            ->whereIn('id_kendaraan', $kendaraanIds)
            ->where('tanggal', '>=', $rentang['mulai'])
            ->where('tanggal', '<', $rentang['selesai'])
            ->groupBy('id_kendaraan', 'tanggal')
            ->get();

        $kendaraan = Kendaraan::whereIn('id', $kendaraanIds)
            ->get(['id', 'nomor_polisi', 'merk', 'tipe'])
            ->map(fn (Kendaraan $k) => [
                'id' => $k->id,
                'nomor_polisi' => $k->nomor_polisi,
                'merk' => $k->merk,
                'tipe' => $k->tipe,
            ])
            ->values()
            ->all();

        $labels = $this->labels($kategori, $tahun, $bulan);

        $data = array_map(fn (string $label) => ['bucket' => $label], $labels);

        foreach ($rows as $row) {
            $tanggal = Carbon::parse($row->tanggal);
            $index = $kategori === 'bulanan'
                ? $tanggal->month - 1
                : $this->mingguKe((int) $tanggal->day) - 1;

            $key = (string) $row->id_kendaraan;
            $data[$index][$key] = (float) $row->total + (float) ($data[$index][$key] ?? 0);
        }

        return [
            'kategori' => $kategori,
            'tahun' => $tahun,
            'bulan' => $kategori === 'mingguan' ? $bulan : null,
            'kendaraan' => $kendaraan,
            'data' => $data,
        ];
    }

    /**
     * @return array{mulai: string, selesai: string}
     */
    private function rentang(string $kategori, int $tahun, ?int $bulan): array
    {
        if ($kategori === 'mingguan') {
            $mulai = Carbon::create($tahun, $bulan ?? 1, 1)->startOfMonth();

            return [
                'mulai' => $mulai->toDateString(),
                'selesai' => $mulai->addMonth()->toDateString(),
            ];
        }

        return [
            'mulai' => "$tahun-01-01",
            'selesai' => ($tahun + 1).'-01-01',
        ];
    }

    /**
     * @return array<int, string>
     */
    private function labels(string $kategori, int $tahun, ?int $bulan): array
    {
        if ($kategori === 'bulanan') {
            return self::BULAN;
        }

        $daysInMonth = Carbon::create($tahun, $bulan ?? 1, 1)->daysInMonth;

        return array_map(
            fn (int $minggu) => 'Minggu '.$minggu,
            range(1, $this->mingguKe($daysInMonth))
        );
    }

    private function mingguKe(int $hariKe): int
    {
        return (int) ceil($hariKe / 7);
    }
}
