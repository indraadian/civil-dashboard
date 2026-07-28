<?php

namespace App\DataTables\Filters;

class TextFilter extends Filter
{
    protected string $type = 'text';
    protected string $operator = 'contains';
}
