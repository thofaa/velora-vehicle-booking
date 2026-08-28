<?php

namespace App\Models;

use Database\Factories\KonsumsiBbmFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['id_kendaraan', 'tanggal', 'jumlah_liter', 'id_pemesanan'])]
class KonsumsiBbm extends Model
{
    /** @use HasFactory<KonsumsiBbmFactory> */
    use HasFactory;

    protected $table = 'konsumsi_bbm';

    protected $primaryKey = 'id_konsumsi';

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'tanggal' => 'date',
            'jumlah_liter' => 'decimal:2',
        ];
    }

    public function kendaraan(): BelongsTo
    {
        return $this->belongsTo(Kendaraan::class, 'id_kendaraan');
    }

    public function pemesanan(): BelongsTo
    {
        return $this->belongsTo(Pemesanan::class, 'id_pemesanan');
    }
}
