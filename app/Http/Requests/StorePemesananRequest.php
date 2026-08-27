<?php

namespace App\Http\Requests;

use App\Models\Driver;
use App\Models\Kendaraan;
use App\Models\Pemesanan;
use App\Models\User;
use App\Services\KetersediaanPemesananService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Validator;

class StorePemesananRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('create', Pemesanan::class);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'tanggal_mulai' => ['required', 'date'],
            'tanggal_selesai' => ['required', 'date'],
            'id_kendaraan' => ['required', 'integer', 'exists:kendaraan,id'],
            'id_driver' => ['required', 'integer', 'exists:driver,id'],
            'penyetuju' => ['required', 'array', 'min:1'],
            'penyetuju.*' => ['required', 'integer', 'exists:users,id'],
        ];
    }

    /**
     * Validasi lintas field dan kondisi data yang tergantung pada state aplikasi.
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($validator->errors()->isNotEmpty()) {
                    return;
                }

                $mulai = Carbon::parse($this->input('tanggal_mulai'));
                $selesai = Carbon::parse($this->input('tanggal_selesai'));
                $idKendaraan = (int) $this->input('id_kendaraan');
                $idDriver = (int) $this->input('id_driver');
                $penyetuju = $this->input('penyetuju', []);

                if ($selesai->lessThanOrEqualTo($mulai)) {
                    $validator->errors()->add(
                        'tanggal_selesai',
                        'Tanggal selesai harus lebih besar dari tanggal mulai.'
                    );
                }

                $kendaraan = Kendaraan::find($idKendaraan);
                $driver = Driver::find($idDriver);

                if ($kendaraan && $kendaraan->status !== 'aktif') {
                    $validator->errors()->add('id_kendaraan', 'Kendaraan tidak dalam kondisi aktif.');
                }

                if ($driver && $driver->status !== 'aktif') {
                    $validator->errors()->add('id_driver', 'Driver tidak dalam kondisi aktif.');
                }

                if ($kendaraan) {
                    $jumlahLevel = (int) $kendaraan->banyak_level_persetujuan;

                    if (count($penyetuju) !== $jumlahLevel) {
                        $validator->errors()->add(
                            'penyetuju',
                            "Jumlah pihak penyetuju harus sama dengan banyak level persetujuan kendaraan ({$jumlahLevel})."
                        );
                    } elseif (count($penyetuju) !== count(array_unique($penyetuju))) {
                        $validator->errors()->add(
                            'penyetuju',
                            'Setiap level harus disetujui oleh pihak penyetuju yang berbeda.'
                        );
                    }
                }

                $this->validasiPenyetuju($validator, $penyetuju);

                if ($selesai->greaterThan($mulai)) {
                    $service = app(KetersediaanPemesananService::class);

                    if ($kendaraan && ! $service->kendaraanTersedia($idKendaraan, $mulai, $selesai)) {
                        $validator->errors()->add(
                            'id_kendaraan',
                            'Kendaraan ini sudah dipesan pada rentang tanggal tersebut.'
                        );
                    }

                    if ($driver && ! $service->driverTersedia($idDriver, $mulai, $selesai)) {
                        $validator->errors()->add(
                            'id_driver',
                            'Driver ini sudah bertugas pada rentang tanggal tersebut.'
                        );
                    }
                }
            },
        ];
    }

    /**
     * @param  array<int, int>  $penyetuju
     */
    private function validasiPenyetuju(Validator $validator, array $penyetuju): void
    {
        $adminId = (int) $this->user()->id;

        foreach ($penyetuju as $level => $userId) {
            $user = User::find($userId);

            if ($user === null) {
                continue;
            }

            if ($user->role !== 'penyetuju') {
                $validator->errors()->add(
                    "penyetuju.{$level}",
                    'User pada level '.($level + 1).' harus memiliki role penyetuju.'
                );
            }

            if ($user->id === $adminId) {
                $validator->errors()->add(
                    "penyetuju.{$level}",
                    'Pihak penyetuju tidak boleh sama dengan admin pembuat pemesanan.'
                );
            }
        }
    }
}
