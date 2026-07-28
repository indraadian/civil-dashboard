<?php

namespace App\DataTables\Filters;

class BooleanFilter extends Filter
{
    protected string $type = 'boolean';
    protected string $operator = 'equals';

    /**
     * Apply boolean filter to query.
     */
    public function apply(mixed $query, string $value): void
    {
        $query->where($this->field, filter_var($value, FILTER_VALIDATE_BOOLEAN));
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'options' => [
                ['value' => '1', 'label' => 'Ya'],
                ['value' => '0', 'label' => 'Tidak'],
            ],
        ]);
    }
}
