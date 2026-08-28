<?php

namespace App\Http\Requests\Dashboard;

use App\Models\Pemesanan;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class JadwalServiceQueryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('viewAny', Pemesanan::class);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'id_kendaraan' => ['required', 'integer', 'exists:kendaraan,id'],
            'bulan' => ['required', 'integer', 'between:1,12'],
            'tahun' => ['required', 'integer', 'between:2000,2100'],
        ];
    }
}
