<?php

namespace App\Models;

use Database\Factories\PersetujuanFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['id_pemesanan', 'level_persetujuan', 'id_pihak_penyetuju', 'status', 'approved_at', 'catatan'])]
class Persetujuan extends Model
{
    /** @use HasFactory<PersetujuanFactory> */
    use HasFactory;

    public const STATUS_PENDING = 'pending';

    public const STATUS_APPROVED = 'approved';

    public const STATUS_REJECTED = 'rejected';

    public const STATUS_DIBATALKAN = 'dibatalkan';

    protected $table = 'persetujuan';

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'approved_at' => 'datetime',
        ];
    }

    public function pemesanan(): BelongsTo
    {
        return $this->belongsTo(Pemesanan::class, 'id_pemesanan');
    }

    public function pihakPenyetuju(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_pihak_penyetuju');
    }
}
