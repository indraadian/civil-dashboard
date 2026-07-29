<?php

namespace App\DataTables\Contracts;

use Illuminate\Database\Eloquent\Builder;

interface DataTableDefinition
{
    /**
     * Base Eloquent query for the datatable.
     */
    public function query(): Builder;

    /**
     * Define the table columns.
     *
     * @return array<int, \App\DataTables\Columns\Column>
     */
    public function columns(): array;

    /**
     * Define available filters.
     *
     * @return array<int, \App\DataTables\Filters\Filter>
     */
    public function filters(): array;

    /**
     * Define searchable column names.
     *
     * @return array<int, string>
     */
    public function searchableColumns(): array;

    /**
     * Define row-level actions.
     *
     * @return array<int, \App\DataTables\Actions\RowAction>
     */
    public function actions(): array;

    /**
     * Define bulk actions.
     *
     * @return array<int, \App\DataTables\Actions\BulkAction>
     */
    public function bulkActions(): array;

    /**
     * Define toolbar-level actions (rendered at top-right of the table header).
     *
     * @return array<int, \App\DataTables\Actions\ToolbarAction>
     */
    public function toolbarActions(): array;

    /**
     * Available per-page options.
     *
     * @return array<int, int>
     */
    public function perPageOptions(): array;

    /**
     * Default items per page.
     */
    public function defaultPerPage(): int;

    /**
     * Default sort field.
     */
    public function defaultSortField(): string;

    /**
     * Default sort direction.
     */
    public function defaultSortDirection(): string;
}
