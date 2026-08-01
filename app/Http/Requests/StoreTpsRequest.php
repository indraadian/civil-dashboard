<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTpsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:50', 'unique:tps,code'],
            'location' => ['nullable', 'string', 'max:500'],
            'total_voters' => ['required', 'integer', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nama TPS wajib diisi.',
            'code.unique' => 'Kode TPS sudah digunakan.',
            'total_voters.required' => 'Total DPT / Pemilih wajib diisi.',
            'total_voters.min' => 'Total DPT tidak boleh kurang dari 0.',
        ];
    }
}
