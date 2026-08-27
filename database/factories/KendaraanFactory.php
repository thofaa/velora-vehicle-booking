<?php

namespace Database\Factories;

use App\Models\Kendaraan;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Kendaraan>
 */
class KendaraanFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nomor_polisi' => fake()->unique()->regexify('[A-Z]{1,2} [0-9]{1,4} [A-Z]{1,3}'),
            'merk' => fake()->randomElement(['Toyota', 'Honda', 'Daihatsu', 'Suzuki', 'Mitsubishi']),
            'tipe' => fake()->randomElement(['Avanza', 'Brio', 'Innova', 'Xenia', 'Pajero']),
            'tahun' => fake()->numberBetween(2010, 2024),
            'warna' => fake()->safeColorName(),
            'jenis_kendaraan' => fake()->randomElement(['Mobil', 'Minibus', 'Bus', 'Truk']),
            'kapasitas' => fake()->numberBetween(2, 40),
            'nomor_mesin' => fake()->unique()->regexify('[A-Z0-9]{12}'),
            'nomor_rangka' => fake()->unique()->regexify('[A-Z0-9]{17}'),
            'tanggal_pajak' => fake()->date(),
            'tanggal_stnk' => fake()->date(),
            'status' => 'aktif',
            'keterangan' => fake()->optional()->sentence(),
            'banyak_level_persetujuan' => 2,
        ];
    }
}
