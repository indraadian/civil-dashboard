<?php

namespace App\DataTables\Filters;

class DateFilter extends Filter
{
    protected string $type = 'date';
    protected string $operator = 'equals';

    /**
     * The date comparison mode: 'exact', 'before', 'after', 'between'.
     */
    protected string $mode = 'exact';

    /**
     * Set the comparison mode.
     */
    public function mode(string $mode): static
    {
        $this->mode = $mode;

        return $this;
    }

    /**
     * Apply the date filter to the query.
     */
    public function apply(mixed $query, string $value): void
    {
        match ($this->mode) {
            'before' => $query->whereDate($this->field, '<=', $value),
            'after' => $query->whereDate($this->field, '>=', $value),
            default => $query->whereDate($this->field, $value),
        };
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'mode' => $this->mode,
        ]);
    }
}
