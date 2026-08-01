<?php

namespace App\Http\Controllers;

use App\DataTables\Definitions\UserDataTable;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Traits\HasDataTable;
use App\Models\Rt;
use App\Models\Rw;
use App\Models\User;
use App\Models\UserLocationScope;
use App\Services\LocationSyncService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class SettingController extends Controller
{
    use HasDataTable;

    /**
     * Tampilkan halaman index settings.
     */
    public function index(): View
    {
        return view('pages.settings.index');
    }

    /**
     * Tampilkan halaman general settings.
     */
    public function general(): View
    {
        return view('pages.settings.general');
    }

    /**
     * Tampilkan daftar user (View).
     */
    public function users(Request $request): View
    {
        $config = $this->dataTableConfig(new UserDataTable());

        $rws = Rw::with(['rts' => fn($q) => $q->where('is_active', true)->orderBy('code', 'asc')])
            ->where('is_active', true)
            ->orderBy('code', 'asc')
            ->get();

        return view('pages.settings.users', compact('rws', 'config'));
    }

    /**
     * Ambil data JSON user untuk DataTable.
     */
    public function usersData(Request $request): JsonResponse
    {
        return $this->dataTableResponse($request, new UserDataTable());
    }

    /**
     * Simpan user baru.
     */
    public function storeUser(StoreUserRequest $request): RedirectResponse
    {
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role,
            'password' => Hash::make($request->password),
        ]);

        $this->syncUserLocationScopes($user, $request->input('scopes', []));

        return redirect()->route('settings.users')
            ->with('success', 'User berhasil ditambahkan.');
    }

    /**
     * Ambil data user untuk form edit (JSON response).
     */
    public function editUser(User $user): JsonResponse
    {
        return response()->json($user->load(['locationScopes.rw', 'locationScopes.rt']));
    }

    /**
     * Update data user.
     */
    public function updateUser(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'role' => $user->isSuperAdmin() ? 'super_admin' : $request->role,
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        $this->syncUserLocationScopes($user, $request->input('scopes', []));

        return redirect()->route('settings.users')
            ->with('success', 'User berhasil diperbarui.');
    }

    /**
     * Hapus user (tidak bisa menghapus diri sendiri).
     */
    public function destroyUser(User $user): JsonResponse
    {
        if ($user->is(auth()->user())) {
            return response()->json([
                'message' => 'Tidak bisa menghapus akun sendiri.',
            ], 422);
        }

        $user->delete();

        return response()->json(['message' => 'User berhasil dihapus.']);
    }

    /**
     * Hapus beberapa user sekaligus.
     */
    public function destroyUsersBulk(Request $request): JsonResponse
    {
        $request->validate([
            'ids'   => ['required', 'array'],
            'ids.*' => ['integer'],
        ]);

        $currentUserId = auth()->id();
        $idsToDelete = array_diff($request->ids, [$currentUserId]);

        User::whereIn('id', $idsToDelete)->delete();

        return response()->json([
            'status'  => 'success',
            'message' => 'Data user terpilih berhasil dihapus.',
        ]);
    }

    /**
     * Jalankan database migration secara manual (khusus super_admin).
     */
    public function migrate(): RedirectResponse
    {
        try {
            $exitCode = Artisan::call('migrate', [
                '--force' => true,
                '--no-interaction' => true,
            ]);

            $output = trim(Artisan::output());

            if ($exitCode === 0) {
                return back()->with('success', $output ?: 'Migrasi berhasil dijalankan.');
            }

            return back()->with('error', $output ?: 'Migrasi gagal dijalankan.');
        } catch (\Throwable $e) {
            Log::error('Manual migration failed.', ['message' => $e->getMessage()]);

            return back()->with('error', 'Terjadi kesalahan saat menjalankan migrasi: ' . $e->getMessage());
        }
    }

    /**
     * Jalankan patch master RW & RT secara manual dari data Civil (khusus super_admin).
     */
    public function patchLocations(LocationSyncService $syncService): RedirectResponse
    {
        try {
            $result = $syncService->syncFromCivils();

            $message = "Patch Master RW & RT berhasil dijalankan! " .
                "RW Baru: {$result['new_rws']}, RT Baru: {$result['new_rts']}, Data Dilewati: {$result['skipped']}.";

            return back()->with('success', $message);
        } catch (\Throwable $e) {
            Log::error('Patch location sync failed.', ['message' => $e->getMessage()]);

            return back()->with('error', 'Terjadi kesalahan saat patch lokasi: ' . $e->getMessage());
        }
    }

    /**
     * Export User data.
     */
    public function exportUsers(Request $request, \App\Services\UserExportService $exportService): RedirectResponse
    {
        $export = $exportService->initiate(
            userId: $request->user()->id,
            filters: $request->all(),
            format: $request->input('format', 'xlsx')
        );

        return back()->with(
            'info',
            "File sedang dibuat di background. ID Export: #{$export->id}. Anda akan diberitahu ketika file siap diunduh."
        );
    }

    /**
     * Import User data from CSV/Excel file.
     */
    public function importUsers(Request $request, \App\Services\UserImportService $importService): RedirectResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:csv,txt,xlsx,xls', 'max:5120'],
        ]);

        $import = $importService->initiate($request);

        return back()->with(
            'info',
            "File sedang diproses di background. ID Import: #{$import->id}. Anda akan diberitahu ketika selesai."
        );
    }

    /**
     * Simpan/sinkronisasi hak akses wilayah user.
     */
    private function syncUserLocationScopes(User $user, array $scopes): void
    {
        $user->locationScopes()->delete();

        foreach ($scopes as $item) {
            if (empty($item['rw_id'])) {
                continue;
            }

            $rwId = (int) $item['rw_id'];
            $rtIds = isset($item['rt_ids']) && is_array($item['rt_ids'])
                ? $item['rt_ids']
                : (isset($item['rt_id']) && $item['rt_id'] !== '' ? [$item['rt_id']] : []);

            if (empty($rtIds)) {
                UserLocationScope::create([
                    'user_id' => $user->id,
                    'rw_id' => $rwId,
                    'rt_id' => null,
                ]);
            } else {
                foreach ($rtIds as $rtId) {
                    UserLocationScope::create([
                        'user_id' => $user->id,
                        'rw_id' => $rwId,
                        'rt_id' => $rtId ? (int) $rtId : null,
                    ]);
                }
            }
        }
    }
}
