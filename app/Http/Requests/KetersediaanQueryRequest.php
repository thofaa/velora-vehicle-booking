<?php

namespace App\Http\Requests;

use App\Models\Pemesanan;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class KetersediaanQueryRequest extends FormRequest
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
            'tanggal_selesai' => ['required', 'date', 'after:tanggal_mulai'],
        ];
    }
}
