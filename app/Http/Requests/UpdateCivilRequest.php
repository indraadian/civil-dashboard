<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCivilRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $user = $this->user();
        $civilParam = $this->route('civil');
        $civilId = is_object($civilParam) ? $civilParam->id : $civilParam;

        if ($user && ($user->isAdmin() || $user->isRw() || $user->isRt())) {
            return [
                'kk' => ['nullable', 'string', 'max:16'],
                'nik' => ['required', 'numeric', 'digits:16', 'unique:civils,nik,' . $civilId],
                'name' => ['required', 'string', 'max:255'],
                'place_of_birth' => ['nullable', 'string', 'max:255'],
                'date_of_birth' => ['required', 'date'],
                'gender' => ['required', 'in:L,P'],
                'rt' => ['required', 'string', 'max:5'],
                'rw' => ['required', 'string', 'max:5'],
                'hamlet' => ['nullable', 'string', 'max:255'],
                'address' => ['required', 'string'],
                'location_type' => ['nullable', 'in:village,housing'],
                'status' => ['nullable', 'in:Militan,Ngambang,Lawan'],
            ];
        }

        return [
            'place_of_birth' => ['nullable', 'string', 'max:255'],
            'hamlet' => ['nullable', 'string', 'max:255'],
            'location_type' => ['nullable', 'in:village,housing'],
            'status' => ['nullable', 'in:Militan,Ngambang,Lawan'],
        ];
    }

    /**
     * Get custom error messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'nik.unique' => 'NIK sudah terdaftar dalam sistem!',
            'nik.digits' => 'NIK harus tepat berisikan 16 digit angka.',
            'nik.numeric' => 'NIK hanya boleh berupa angka.',
            'required' => 'Kolom :attribute wajib diisi!',
        ];
    }
}
