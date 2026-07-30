<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ExportCivilRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create', \App\Models\CivilExport::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'format' => ['nullable', 'in:xlsx,csv'],
            'status' => ['nullable', 'string', 'max:50'],
            'hamlet' => ['nullable', 'string', 'max:100'],
            'rt'     => ['nullable', 'string', 'max:10'],
            'rw'     => ['nullable', 'string', 'max:10'],
        ];
    }

    /**
     * Mendapatkan format file export yang diminta (default: xlsx).
     */
    public function exportFormat(): string
    {
        return $this->input('format', 'xlsx');
    }

    /**
     * Mendapatkan filter yang akan diterapkan ke query export.
     *
     * @return array<string, string|null>
     */
    public function filters(): array
    {
        return array_filter([
            'status' => $this->input('status'),
            'hamlet' => $this->input('hamlet'),
            'rt'     => $this->input('rt'),
            'rw'     => $this->input('rw'),
        ]);
    }
}
