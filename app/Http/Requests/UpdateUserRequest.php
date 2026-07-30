<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRequest extends FormRequest
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
        $userParam = $this->route('user');
        $userId = is_object($userParam) ? $userParam->id : $userParam;

        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,' . $userId],
            'role' => ['required', 'in:admin,user'],
            'scopes' => ['nullable', 'array'],
            'scopes.*.rw_id' => ['nullable', 'exists:rws,id'],
            'scopes.*.rt_ids' => ['nullable', 'array'],
            'scopes.*.rt_ids.*' => ['nullable', 'exists:rts,id'],
        ];

        if ($this->filled('password')) {
            $rules['password'] = ['nullable', 'string', 'min:6', 'confirmed'];
        }

        return $rules;
    }
}
