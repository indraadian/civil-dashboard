<?php

namespace App\DataTables\Filters;

class Filter
{
    protected string $field;
    protected string $label;
    protected string $type = 'text';
    protected string $operator = 'contains';

    public function __construct(string $field)
    {
        $this->field = $field;
        $this->label = ucfirst(str_replace('_', ' ', $field));
    }

    /**
     * Create a new filter instance.
     */
    public static function make(string $field): static
    {
        return new static($field);
    }

    /**
     * Set the filter label.
     */
    public function label(string $label): static
    {
        $this->label = $label;

        return $this;
    }

    /**
     * Set the filter comparison operator.
     */
    public function operator(string $operator): static
    {
        $this->operator = $operator;

        return $this;
    }

    /**
     * Apply this filter to the query.
     */
    public function apply(mixed $query, string $value): void
    {
        if ($this->operator === 'contains') {
            $query->where($this->field, 'like', "%{$value}%");
        } elseif ($this->operator === 'equals') {
            $query->where($this->field, $value);
        }
    }

    /**
     * Serialize to array for Blade/Alpine.js config.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'field' => $this->field,
            'label' => $this->label,
            'type' => $this->type,
            'operator' => $this->operator,
        ];
    }

    public function getField(): string
    {
        return $this->field;
    }

    public function getOperator(): string
    {
        return $this->operator;
    }
}
