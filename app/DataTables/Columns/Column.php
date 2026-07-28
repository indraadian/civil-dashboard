<?php

namespace App\DataTables\Columns;

class Column
{
    protected string $field;
    protected string $label;
    protected string $type = 'text';
    protected bool $sortable = false;
    protected bool $visible = true;
    protected bool $toggleable = true;
    protected ?string $prefix = null;
    protected ?string $suffix = null;
    protected ?\Closure $computedCallback = null;
    protected ?\Closure $formatterCallback = null;

    public function __construct(string $field)
    {
        $this->field = $field;
        $this->label = ucfirst(str_replace('_', ' ', $field));
    }

    /**
     * Create a new column instance.
     */
    public static function make(string $field): static
    {
        return new static($field);
    }

    /**
     * Set the column label.
     */
    public function label(string $label): static
    {
        $this->label = $label;

        return $this;
    }

    /**
     * Enable sorting on this column.
     */
    public function sortable(bool $sortable = true): static
    {
        $this->sortable = $sortable;

        return $this;
    }

    /**
     * Set initial visibility.
     */
    public function visible(bool $visible = true): static
    {
        $this->visible = $visible;

        return $this;
    }

    /**
     * Allow toggling this column's visibility.
     */
    public function toggleable(bool $toggleable = true): static
    {
        $this->toggleable = $toggleable;

        return $this;
    }

    /**
     * Add a prefix to the displayed value.
     */
    public function prefix(string $prefix): static
    {
        $this->prefix = $prefix;

        return $this;
    }

    /**
     * Add a suffix to the displayed value.
     */
    public function suffix(string $suffix): static
    {
        $this->suffix = $suffix;

        return $this;
    }

    /**
     * Set a computed value callback (the column is not a real DB field).
     */
    public function computed(\Closure $callback): static
    {
        $this->computedCallback = $callback;

        return $this;
    }

    /**
     * Set a custom formatter callback.
     */
    public function formatUsing(\Closure $callback): static
    {
        $this->formatterCallback = $callback;

        return $this;
    }

    /**
     * Resolve the value for a given row.
     */
    public function resolveValue(mixed $row): mixed
    {
        if ($this->computedCallback) {
            return call_user_func($this->computedCallback, $row);
        }

        $value = data_get($row, $this->field);

        if ($this->formatterCallback) {
            $value = call_user_func($this->formatterCallback, $value, $row);
        }

        if ($this->prefix) {
            $value = $this->prefix . $value;
        }

        if ($this->suffix) {
            $value .= $this->suffix;
        }

        return $value;
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
            'sortable' => $this->sortable,
            'visible' => $this->visible,
            'toggleable' => $this->toggleable,
        ];
    }

    // --- Getters ---

    public function getField(): string
    {
        return $this->field;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function isSortable(): bool
    {
        return $this->sortable;
    }

    public function isVisible(): bool
    {
        return $this->visible;
    }

    public function isComputed(): bool
    {
        return $this->computedCallback !== null;
    }
}
