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

        if ($user && $user->role === 'admin') {
            return [
                'nik' => ['required', 'numeric', 'digits:16', 'unique:civils,nik,' . $this->route('civil')],
                'kk' => ['nullable', 'string', 'max:16'],
                'name' => ['required', 'string', 'max:255'],
                'hamlet' => ['nullable', 'string', 'max:255'],
                'location_type' => ['required', 'in:village,housing'],
                'rt' => ['required', 'string', 'max:5'],
                'rw' => ['required', 'string', 'max:5'],
                'address' => ['required', 'string'],
                'date_of_birth' => ['required', 'date'],
                'gender' => ['required', 'in:L,P'],
                'status' => ['required', 'in:Militan,Ngambang,Lawan'],
            ];
        }

        return [
            'hamlet' => ['nullable', 'string', 'max:255'],
            'location_type' => ['required', 'in:village,housing'],
            'status' => ['required', 'in:Militan,Ngambang,Lawan'],
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
