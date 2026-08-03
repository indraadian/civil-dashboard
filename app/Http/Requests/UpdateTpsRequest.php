<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTpsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $param = $this->route('tps') ?? $this->route('tp');
        $tpsId = is_object($param) ? $param->id : ($param ?? $this->input('id'));

        return [
            'name' => ['required', 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:50', Rule::unique('tps', 'code')->ignore($tpsId)->whereNull('deleted_at')],
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
