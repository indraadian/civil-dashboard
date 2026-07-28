<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class SettingController extends Controller
{
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
     * Tampilkan daftar user dengan pencarian.
     */
    public function users(Request $request): View|JsonResponse
    {
        $query = $request->input('search');

        $users = User::query()
            ->when($query, function ($q) use ($query) {
                $q->where(function ($subQuery) use ($query) {
                    $subQuery->where('name', 'like', "%{$query}%")
                        ->orWhere('email', 'like', "%{$query}%")
                        ->orWhere('role', 'like', "%{$query}%");
                });
            })
            ->orderBy('created_at', 'desc')
            ->get();

        if ($request->expectsJson() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
            return response()->json([
                'html' => view('pages.settings.partials.user-table', compact('users'))->render(),
            ]);
        }

        return view('pages.settings.users', compact('users', 'query'));
    }

    /**
     * Simpan user baru.
     */
    public function storeUser(StoreUserRequest $request): RedirectResponse
    {
        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role,
            'password' => Hash::make($request->password),
        ]);

        return redirect()->route('settings.users')
            ->with('success', 'User berhasil ditambahkan.');
    }

    /**
     * Ambil data user untuk form edit (JSON response).
     */
    public function editUser(User $user): JsonResponse
    {
        return response()->json($user);
    }

    /**
     * Update data user.
     */
    public function updateUser(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role,
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

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
     * Jalankan database migration secara manual.
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
}
