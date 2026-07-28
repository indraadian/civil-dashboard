<?php

namespace App\DataTables\Columns;

class BadgeColumn extends Column
{
    protected string $type = 'badge';

    /**
     * Mapping of value => ['label' => '...', 'color' => '...']
     *
     * @var array<string, array{label: string, color: string}>
     */
    protected array $mapping = [];

    /**
     * Define the badge mapping.
     *
     * @param  array<string, array{label: string, color: string}>  $mapping
     */
    public function mapping(array $mapping): static
    {
        $this->mapping = $mapping;

        return $this;
    }

    /**
     * @return array<string, array{label: string, color: string}>
     */
    public function getMapping(): array
    {
        return $this->mapping;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'mapping' => $this->mapping,
        ]);
    }
}
