<?php

namespace App\Http\Requests;

use App\Models\Persetujuan;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Validator;

class ApprovePersetujuanRequest extends FormRequest
{
    public function authorize(): bool
    {
        $persetujuan = $this->route('persetujuan');

        return $persetujuan instanceof Persetujuan
            && Gate::allows('process', $persetujuan);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [];
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                /** @var Persetujuan $persetujuan */
                $persetujuan = $this->route('persetujuan');

                if (! $this->bisaDiproses($persetujuan, $validator)) {
                    return;
                }

                $pemesanan = $persetujuan->pemesanan;

                if ($pemesanan->status === 'dibatalkan') {
                    $validator->errors()->add('persetujuan', 'Pemesanan telah dibatalkan, tidak dapat diproses.');
                }
            },
        ];
    }

    protected function bisaDiproses(Persetujuan $persetujuan, Validator $validator): bool
    {
        if ($persetujuan->status !== 'pending') {
            $validator->errors()->add('persetujuan', 'Persetujuan ini sudah diproses sebelumnya.');

            return false;
        }

        if ($persetujuan->level_persetujuan > 1) {
            $levelSebelumnya = $persetujuan->pemesanan->persetujuan()
                ->where('level_persetujuan', '<', $persetujuan->level_persetujuan)
                ->get();

            $blmDisetujui = $levelSebelumnya->first(fn ($item) => $item->status !== 'approved');

            if ($blmDisetujui !== null) {
                $validator->errors()->add(
                    'persetujuan',
                    'Menunggu persetujuan level sebelumnya belum selesai.'
                );

                return false;
            }
        }

        return true;
    }
}
