<?php

namespace App\Http\Requests\Dashboard;

use App\Models\Pemesanan;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class KonsumsiBbmQueryRequest extends FormRequest
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
            'kendaraan_ids' => ['required', 'array', 'min:1', 'max:5'],
            'kendaraan_ids.*' => ['integer', 'distinct', 'exists:kendaraan,id'],
            'kategori' => ['required', 'in:bulanan,mingguan'],
            'tahun' => ['required', 'integer', 'between:2000,2100'],
            'bulan' => ['nullable', 'integer', 'between:1,12', 'required_if:kategori,mingguan'],
        ];
    }
}
