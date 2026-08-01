<?php

namespace App\Http\Controllers;

use App\DataTables\Definitions\RoleDataTable;
use App\Http\Traits\HasDataTable;
use App\Services\PermissionSyncService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    use HasDataTable;

    /**
     * Display Roles data table & management page.
     */
    public function index(): View
    {
        $config = $this->dataTableConfig(new RoleDataTable());
        $modulePermissions = PermissionSyncService::getModulePermissions();
        $allPermissions = Permission::orderBy('name', 'asc')->get();

        return view('pages.settings.roles', compact('config', 'modulePermissions', 'allPermissions'));
    }

    /**
     * Get JSON data for Role DataTable.
     */
    public function data(Request $request): JsonResponse
    {
        return $this->dataTableResponse($request, new RoleDataTable());
    }

    /**
     * Store a new Role and assign permissions.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:roles,name'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string'],
        ]);

        $role = Role::create([
            'name' => trim($request->name),
            'guard_name' => 'web',
        ]);

        if ($request->has('permissions')) {
            $role->syncPermissions($request->input('permissions', []));
        }

        return redirect()->route('settings.roles')
            ->with('success', "Role '{$role->name}' berhasil dibuat.");
    }

    /**
     * Get role data for edit modal (JSON).
     */
    public function edit(Role $role): JsonResponse
    {
        return response()->json([
            'id' => $role->id,
            'name' => $role->name,
            'permissions' => $role->permissions->pluck('name')->toArray(),
        ]);
    }

    /**
     * Update role details and permissions.
     */
    public function update(Request $request, Role $role): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:roles,name,' . $role->id],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string'],
        ]);

        if ($role->name !== 'Super Admin') {
            $role->update(['name' => trim($request->name)]);
        }

        if ($role->name !== 'Super Admin') {
            $role->syncPermissions($request->input('permissions', []));
        }

        return redirect()->route('settings.roles')
            ->with('success', "Role '{$role->name}' berhasil diperbarui.");
    }

    /**
     * Delete role (Super Admin role protected).
     */
    public function destroy(Role $role): JsonResponse
    {
        if ($role->name === 'Super Admin') {
            return response()->json([
                'message' => 'Role Super Admin tidak dapat dihapus.',
            ], 422);
        }

        if ($role->users()->count() > 0) {
            return response()->json([
                'message' => "Role '{$role->name}' masih digunakan oleh {$role->users()->count()} user.",
            ], 422);
        }

        $role->delete();

        return response()->json(['message' => "Role '{$role->name}' berhasil dihapus."]);
    }
}
