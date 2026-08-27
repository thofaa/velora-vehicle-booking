<?php

namespace App\Models;

use Database\Factories\KendaraanFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['nomor_polisi', 'merk', 'tipe', 'tahun', 'warna', 'jenis_kendaraan', 'kapasitas', 'nomor_mesin', 'nomor_rangka', 'tanggal_pajak', 'tanggal_stnk', 'status', 'keterangan', 'banyak_level_persetujuan'])]
class Kendaraan extends Model
{
    /** @use HasFactory<KendaraanFactory> */
    use HasFactory;

    protected $table = 'kendaraan';

    #[Scope]
    protected function aktif(Builder $query): Builder
    {
        return $query->where('status', 'aktif');
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'tahun' => 'integer',
            'kapasitas' => 'integer',
            'banyak_level_persetujuan' => 'integer',
            'tanggal_pajak' => 'date',
            'tanggal_stnk' => 'date',
        ];
    }

    public function pemesanan(): HasMany
    {
        return $this->hasMany(Pemesanan::class, 'id_kendaraan');
    }
}
