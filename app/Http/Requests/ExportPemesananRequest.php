<?php

namespace App\Http\Requests;

use App\Models\Pemesanan;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class ExportPemesananRequest extends FormRequest
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
            'dari' => ['required', 'date'],
            'hingga' => ['required', 'date', 'after_or_equal:dari'],
        ];
    }
}
