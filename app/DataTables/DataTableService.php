<?php

namespace App\DataTables;

use App\DataTables\Columns\AvatarColumn;
use App\DataTables\Columns\Column;
use App\DataTables\Columns\DateColumn;
use App\DataTables\Contracts\DataTableDefinition;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DataTableService
{
    protected DataTableDefinition $definition;

    public function __construct(DataTableDefinition $definition)
    {
        $this->definition = $definition;
    }

    /**
     * Handle a datatable data request: search, filter, sort, paginate.
     */
    public function handle(Request $request): JsonResponse
    {
        $query = $this->definition->query();
        $perPage = $request->input('per_page', $this->definition->defaultPerPage());
        $search = $request->input('search');
        $filters = $request->input('filters', []);
        $sortField = $request->input('sort_field', $this->definition->defaultSortField());
        $sortDirection = $request->input('sort_direction', $this->definition->defaultSortDirection());

        // Apply global search
        if ($search) {
            $searchableColumns = $this->definition->searchableColumns();
            $query->where(function ($q) use ($search, $searchableColumns) {
                foreach ($searchableColumns as $column) {
                    $q->orWhere($column, 'like', "%{$search}%");
                }
            });
        }

        // Apply filters
        $this->applyFilters($query, $filters);

        // dd($query);

        // Apply sorting (only for real DB columns, not computed)
        $sortableFields = collect($this->definition->columns())
            ->filter(fn(Column $col) => $col->isSortable() && !$col->isComputed())
            ->map(fn(Column $col) => $col->getField())
            ->values()
            ->toArray();

        if (in_array($sortField, $sortableFields)) {
            $direction = in_array(strtolower($sortDirection), ['asc', 'desc']) ? $sortDirection : 'asc';
            $query->orderBy($sortField, $direction);
        } else {
            $query->orderBy(
                $this->definition->defaultSortField(),
                $this->definition->defaultSortDirection()
            );
        }

        $paginated = $query->paginate($perPage);

        // Transform rows using column definitions
        $rows = $paginated->getCollection()->map(function ($row) {
            return $this->transformRow($row);
        });

        return response()->json([
            'data' => $rows,
            'meta' => [
                'current_page' => $paginated->currentPage(),
                'last_page' => $paginated->lastPage(),
                'per_page' => $paginated->perPage(),
                'total' => $paginated->total(),
            ],
        ]);
    }

    /**
     * Apply filter parameters to the query.
     */
    protected function applyFilters(mixed $query, array $filters): void
    {
        $filterDefinitions = collect($this->definition->filters())
            ->keyBy(fn($filter) => $filter->getField());

        foreach ($filters as $filter) {
            if (!is_array($filter)) {
                continue;
            }

            $field = $filter['field'] ?? null;
            $value = trim($filter['value'] ?? '');

            if (!$field || $value === '' || !$filterDefinitions->has($field)) {
                continue;
            }

            $filterDefinitions->get($field)->apply($query, $value);
        }
    }

    /**
     * Transform an Eloquent model row into a flat array using column definitions.
     *
     * @return array<string, mixed>
     */
    protected function transformRow(mixed $row): array
    {
        $data = ['id' => $row->id];

        foreach ($this->definition->columns() as $column) {
            $field = $column->getField();

            if ($column->getType() === 'action') {
                continue;
            }

            if ($column instanceof AvatarColumn) {
                $initialsCallback = $column->getInitialsCallback();
                $colorByField = $column->getColorByField();
                $colorMapping = $column->getColorMapping();

                $data[$field] = $column->resolveValue($row);
                $data[$field . '__initials'] = $initialsCallback
                    ? call_user_func($initialsCallback, $row)
                    : strtoupper(substr((string) data_get($row, $field), 0, 2));

                if ($colorByField) {
                    $colorKey = data_get($row, $colorByField);
                    $colors = $colorMapping[$colorKey] ?? ['bg' => 'bg-gray-100', 'text' => 'text-gray-500'];
                    $data[$field . '__avatar_bg'] = $colors['bg'];
                    $data[$field . '__avatar_color'] = $colors['text'];
                }

                continue;
            }

            if ($column instanceof DateColumn) {
                $rawValue = data_get($row, $field);
                $data[$field] = $rawValue;
                $data[$field . '__raw'] = $rawValue;

                continue;
            }

            $data[$field] = $column->resolveValue($row);
        }

        return $data;
    }

    /**
     * Build the full config array for Blade/Alpine.js.
     *
     * @return array<string, mixed>
     */
    public static function buildConfig(DataTableDefinition $definition): array
    {
        $user = auth()->user();

        return [
            'columns' => collect($definition->columns())
                ->map(fn(Column $col) => $col->toArray())
                ->values()
                ->toArray(),
            'filters' => collect($definition->filters())
                ->map(fn($filter) => $filter->toArray())
                ->values()
                ->toArray(),
            'actions' => collect($definition->actions())
                ->filter(fn($action) => $action->isAuthorized())
                ->map(fn($action) => $action->toArray())
                ->values()
                ->toArray(),
            'bulkActions' => collect($definition->bulkActions())
                ->filter(fn($action) => $action->isAuthorized())
                ->map(fn($action) => $action->toArray())
                ->values()
                ->toArray(),
            'toolbarActions' => collect($definition->toolbarActions())
                ->filter(fn($action) => $action->isAuthorized())
                ->map(fn($action) => $action->toArray())
                ->values()
                ->toArray(),
            'perPageOptions' => $definition->perPageOptions(),
            'defaultPerPage' => $definition->defaultPerPage(),
            'defaultSortField' => $definition->defaultSortField(),
            'defaultSortDirection' => $definition->defaultSortDirection(),
            'searchableColumns' => $definition->searchableColumns(),
        ];
    }
}
