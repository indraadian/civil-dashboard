<?php

namespace App\DataTables\Filters;

class SelectFilter extends Filter
{
    protected string $type = 'select';
    protected string $operator = 'equals';

    /**
     * Options for the select dropdown.
     * Can be ['value1', 'value2'] or ['key' => 'Label']
     *
     * @var array<string|int, string>
     */
    protected array $options = [];

    /**
     * Set the select options.
     *
     * @param  array<string|int, string>  $options
     */
    public function options(array $options): static
    {
        $this->options = $options;

        return $this;
    }

    /**
     * @return array<string|int, string>
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        // Normalize options to [{value, label}] format
        $normalized = [];
        foreach ($this->options as $key => $value) {
            if (is_int($key)) {
                $normalized[] = ['value' => $value, 'label' => $value];
            } else {
                $normalized[] = ['value' => $key, 'label' => $value];
            }
        }

        return array_merge(parent::toArray(), [
            'options' => $normalized,
        ]);
    }
}
