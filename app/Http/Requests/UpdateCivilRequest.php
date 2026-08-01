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

        $rules = [
            'kk' => ['nullable', 'string', 'max:16'],
            'place_of_birth' => ['nullable', 'string', 'max:255'],
            'hamlet' => ['nullable', 'string', 'max:255'],
            'location_type' => ['nullable', 'in:village,housing'],
            'status' => ['nullable', 'in:Militan,Ngambang,Lawan'],
        ];

        // Only enforce required validations if the input field is present in the request
        if ($this->has('nik')) {
            $rules['nik'] = ['required', 'numeric', 'digits:16', 'unique:civils,nik,' . $civilId];
        }
        if ($this->has('name')) {
            $rules['name'] = ['required', 'string', 'max:255'];
        }
        if ($this->has('date_of_birth')) {
            $rules['date_of_birth'] = ['required', 'date'];
        }
        if ($this->has('gender')) {
            $rules['gender'] = ['required', 'in:L,P'];
        }
        if ($this->has('rt')) {
            $rules['rt'] = ['required', 'string', 'max:5'];
        }
        if ($this->has('rw')) {
            $rules['rw'] = ['required', 'string', 'max:5'];
        }
        if ($this->has('address')) {
            $rules['address'] = ['required', 'string'];
        }

        return $rules;
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
