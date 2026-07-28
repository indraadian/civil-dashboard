<?php

namespace App\Http\Traits;

use App\DataTables\Contracts\DataTableDefinition;
use App\DataTables\DataTableService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

trait HasDataTable
{
    /**
     * Handle a datatable data request using a definition.
     */
    protected function dataTableResponse(Request $request, DataTableDefinition $definition): JsonResponse
    {
        $service = new DataTableService($definition);

        return $service->handle($request);
    }

    /**
     * Build the datatable config for passing to Blade/Alpine.js.
     *
     * @return array<string, mixed>
     */
    protected function dataTableConfig(DataTableDefinition $definition): array
    {
        return DataTableService::buildConfig($definition);
    }
}
