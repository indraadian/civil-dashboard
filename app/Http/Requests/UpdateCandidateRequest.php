<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCandidateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $param = $this->route('candidate');
        $candidateId = is_object($param) ? $param->id : ($param ?? $this->input('id'));

        return [
            'number' => [
                'required',
                'integer',
                'min:1',
                Rule::unique('candidates', 'number')->ignore($candidateId)->whereNull('deleted_at'),
            ],
            'name' => ['required', 'string', 'max:255'],
            'photo' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
