<?php

namespace Database\Seeders;

use App\Models\Driver;
use App\Models\JadwalService;
use App\Models\Kendaraan;
use App\Models\KonsumsiBbm;
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

        User::factory()->penyetuju()->create([
            'name' => 'Penyetuju Satu',
            'email' => 'penyetuju@example.com',
        ]);

        User::factory()->penyetuju()->create();

        Kendaraan::factory()->count(count(KendaraanFactory::FLEET))->sequence(
            fn (Sequence $sequence) => KendaraanFactory::FLEET[$sequence->index],
        )->create();
        Driver::factory()->count(5)->create();

        User::factory()->create();

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

        foreach (range(1, 3) as $i) {
            JadwalService::factory()->create([
                'id_kendaraan' => $kendaraanIds->random(),
                'tanggal_service' => now()->setDate($tahun, now()->month, rand(1, 28)),
                'status' => $i <= 2 ? 'selesai' : 'terjadwal',
            ]);
        }
    }
}
