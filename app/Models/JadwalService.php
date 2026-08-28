<?php

namespace App\Models;

use Database\Factories\JadwalServiceFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['id_kendaraan', 'tanggal_service', 'jenis_service', 'status'])]
class JadwalService extends Model
{
    /** @use HasFactory<JadwalServiceFactory> */
    use HasFactory;

    public const STATUS_TERJADWAL = 'terjadwal';

    public const STATUS_SELESAI = 'selesai';

    public const STATUS_TERLEWAT = 'terlewat';

    protected $table = 'jadwal_service';

    protected $primaryKey = 'id_jadwal_service';

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'tanggal_service' => 'date',
        ];
    }

    public function kendaraan(): BelongsTo
    {
        return $this->belongsTo(Kendaraan::class, 'id_kendaraan');
    }
}
