<?php

namespace App\Http\Requests\Dashboard;

use App\Models\Pemesanan;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class RiwayatPemakaianQueryRequest extends FormRequest
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
            'tahun' => ['required', 'integer', 'between:2000,2100'],
        ];
    }
}
