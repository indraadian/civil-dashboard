<?php

namespace App\DataTables\Columns;

class AvatarColumn extends Column
{
    protected string $type = 'avatar';

    /**
     * Callback to generate initials from row data.
     */
    protected ?\Closure $initialsCallback = null;

    /**
     * Mapping of a field value to avatar colors.
     * e.g. ['housing' => ['bg' => 'bg-blue-100', 'text' => 'text-blue-500']]
     *
     * @var array<string, array{bg: string, text: string}>
     */
    protected array $colorMapping = [];

    /**
     * The field name used for color lookup.
     */
    protected ?string $colorByField = null;

    /**
     * Set the initials callback.
     */
    public function initials(\Closure $callback): static
    {
        $this->initialsCallback = $callback;

        return $this;
    }

    /**
     * Set color mapping based on a field.
     *
     * @param  array<string, array{bg: string, text: string}>  $mapping
     */
    public function colorBy(string $field, array $mapping): static
    {
        $this->colorByField = $field;
        $this->colorMapping = $mapping;

        return $this;
    }

    public function getInitialsCallback(): ?\Closure
    {
        return $this->initialsCallback;
    }

    public function getColorByField(): ?string
    {
        return $this->colorByField;
    }

    /**
     * @return array<string, array{bg: string, text: string}>
     */
    public function getColorMapping(): array
    {
        return $this->colorMapping;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'colorByField' => $this->colorByField,
            'colorMapping' => $this->colorMapping,
        ]);
    }
}
