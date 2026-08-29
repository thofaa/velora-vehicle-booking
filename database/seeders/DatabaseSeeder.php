<?php

namespace Database\Seeders;

use App\Models\Driver;
use App\Models\JadwalService;
use App\Models\Kendaraan;
use App\Models\KonsumsiBbm;
use App\Models\Pemesanan;
use App\Models\User;
use Database\Factories\KendaraanFactory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory()->admin()->create([
            'name' => 'Admin Utama',
            'email' => 'admin@example.com',
        ]);
        User::factory()->admin()->count(2)->create();

        User::factory()->penyetuju()->create([
            'name' => 'Penyetuju Satu',
            'email' => 'penyetuju@example.com',
        ]);
        User::factory()->penyetuju()->count(7)->create();

        Kendaraan::factory()->count(count(KendaraanFactory::FLEET))->sequence(
            fn (Sequence $sequence) => KendaraanFactory::FLEET[$sequence->index],
        )->create();
        Driver::factory()->count(9)->create();

        $adminIds = User::where('role', 'admin')->pluck('id');
        $penyetujuIds = User::where('role', 'penyetuju')->pluck('id');
        $driverIds = Driver::pluck('id');
        $kendaraanIds = Kendaraan::pluck('id');
        $tahun = now()->year;

        foreach ($kendaraanIds as $kendaraanId) {
            foreach (range(1, 12) as $bulan) {
                KonsumsiBbm::factory()->create([
                    'id_kendaraan' => $kendaraanId,
                    'tanggal' => now()->setDate($tahun, $bulan, fake()->numberBetween(1, 28)),
                    'jumlah_liter' => fake()->randomFloat(2, 30, 120),
                ]);
            }
        }

        foreach (range(1, 30) as $i) {
            JadwalService::factory()->create([
                'id_kendaraan' => $kendaraanIds->random(),
                'tanggal_service' => now()->setDate(
                    $tahun,
                    fake()->numberBetween(1, 12),
                    fake()->numberBetween(1, 28),
                ),
                'status' => fake()->randomElement(['terjadwal', 'selesai']),
            ]);
        }

        //Dummy pemesanan disetujui sepanjang tahun untuk widget Riwayat Pemakaian (heatmap).
        for ($i = 0; $i < 60; $i++) {
            $awal = now()->setDate($tahun, fake()->numberBetween(1, 12), fake()->numberBetween(1, 28));
            Pemesanan::factory()->create([
                'id_kendaraan' => $kendaraanIds->random(),
                'id_driver' => $driverIds->random(),
                'id_admin' => $adminIds->random(),
                'tanggal_mulai' => $awal,
                'tanggal_selesai' => $awal->copy()->addDays(fake()->numberBetween(1, 3)),
                'status' => Pemesanan::STATUS_DISETUJUI,
            ]);
        }
    }
}
