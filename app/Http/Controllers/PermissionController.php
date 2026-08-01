<?php

namespace App\Http\Controllers;

use App\DataTables\Definitions\PermissionDataTable;
use App\Http\Traits\HasDataTable;
use App\Services\PermissionSyncService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PermissionController extends Controller
{
    use HasDataTable;

    /**
     * Display Permission list & management page.
     */
    public function index(): View
    {
        $config = $this->dataTableConfig(new PermissionDataTable());

        return view('pages.settings.permissions', compact('config'));
    }

    /**
     * Get JSON dataset for Permission DataTable.
     */
    public function data(Request $request): JsonResponse
    {
        return $this->dataTableResponse($request, new PermissionDataTable());
    }

    /**
     * Re-synchronize permissions from module configurations.
     */
    public function sync(PermissionSyncService $syncService): RedirectResponse
    {
        $count = $syncService->sync();

        return redirect()->route('settings.permissions')
            ->with('success', "Berhasil me-refresh dan mensinkronkan {$count} permission dari modul.");
    }
}
