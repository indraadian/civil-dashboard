<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateQuickCountRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $quickCount = $this->route('quickCount') ?? $this->route('quick_count');
        $id = is_object($quickCount) ? $quickCount->id : $quickCount;

        return [
            'tps_id' => ['required', 'exists:tps,id', Rule::unique('quick_counts', 'tps_id')->ignore($id)],
            'vote_count' => ['required', 'integer', 'min:0'],
            'total_voters' => ['required', 'integer', 'min:0', 'gte:vote_count'],
            'c1_photo' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:4096'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'tps_id.required' => 'TPS wajib dipilih.',
            'tps_id.exists' => 'TPS tidak valid.',
            'tps_id.unique' => 'Data Quick Count untuk TPS ini sudah ada.',
            'vote_count.required' => 'Jumlah suara wajib diisi.',
            'vote_count.min' => 'Jumlah suara tidak boleh negatif.',
            'total_voters.required' => 'Total pemilih TPS wajib diisi.',
            'total_voters.min' => 'Total pemilih TPS tidak boleh negatif.',
            'total_voters.gte' => 'Total pemilih tidak boleh kurang dari jumlah suara.',
            'c1_photo.image' => 'File Foto C1 harus berupa gambar.',
            'c1_photo.mimes' => 'Format gambar yang diperbolehkan: jpeg, jpg, png, webp.',
            'c1_photo.max' => 'Ukuran foto C1 maksimal 4MB.',
        ];
    }
}
