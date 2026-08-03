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
        $param = $this->route('quickCount') ?? $this->route('quick_count');
        $quickCountId = is_object($param) ? $param->id : ($param ?? $this->input('id'));

        return [
            'tps_id' => [
                'required',
                'exists:tps,id',
                Rule::unique('quick_counts', 'tps_id')->ignore($quickCountId)->whereNull('deleted_at'),
            ],
            'officer_name' => ['required', 'string', 'max:255'],
            'officer_phone' => ['required', 'string', 'max:50'],
            'c1_photo' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:5120'],
            'invalid_votes' => ['required', 'integer', 'min:0'],
            'total_voters' => ['required', 'integer', 'min:0'],
            'votes' => ['required', 'array'],
            'votes.*' => ['required', 'integer', 'min:0'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $votes = (array) $this->input('votes', []);
            $validVotesSum = array_sum(array_map('intval', $votes));
            $invalidVotes = (int) $this->input('invalid_votes', 0);
            $totalVoters = (int) $this->input('total_voters', 0);

            if (($validVotesSum + $invalidVotes) !== $totalVoters) {
                $validator->errors()->add(
                    'total_voters',
                    "Total perolehan suara calon ({$validVotesSum}) + suara tidak sah ({$invalidVotes}) = " .
                    ($validVotesSum + $invalidVotes) . " tidak sama dengan Total Pengguna Hak Pilih ({$totalVoters})."
                );
            }
        });
    }
}
