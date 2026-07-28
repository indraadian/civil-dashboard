<?php

namespace App\DataTables\Columns;

class ActionColumn extends Column
{
    protected string $type = 'action';
    protected string $field = '__actions';
    protected bool $sortable = false;
    protected bool $toggleable = false;

    public function __construct(string $field = '__actions')
    {
        parent::__construct($field);
        $this->label = '';
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'type' => 'action',
        ]);
    }

    /**
     * Create a new column instance.
     */
    public static function make(string $field = 'actions'): static
    {
        return new static($field);
    }
}
