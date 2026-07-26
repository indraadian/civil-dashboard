<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class SettingController extends Controller
{
    public function index()
    {
        return view('pages.settings.index');
    }

    public function general()
    {
        return view('pages.settings.general');
    }

    public function users()
    {
        $users = User::orderBy('created_at', 'desc')->get();

        return view('pages.settings.users', compact('users'));
    }

    public function storeUser(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6'],
            'role' => ['required', 'in:admin,user'],
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role,
            'password' => Hash::make($request->password),
        ]);

        return redirect()->route('settings.users')->with('success', 'User berhasil ditambahkan.');
    }

    public function editUser(int $id)
    {
        $user = User::findOrFail($id);

        return response()->json($user);
    }

    public function updateUser(Request $request, int $id)
    {
        $user = User::findOrFail($id);

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,' . $id],
            'role' => ['required', 'in:admin,user'],
        ]);

        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role,
        ];

        if ($request->filled('password')) {
            $request->validate(['password' => ['nullable', 'string', 'min:6']]);
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        return redirect()->route('settings.users')->with('success', 'User berhasil diperbarui.');
    }

    public function destroyUser(int $id)
    {
        $user = User::findOrFail($id);
        if (auth()->id() === $user->id) {
            return response()->json(['message' => 'Tidak bisa menghapus akun sendiri.'], 422);
        }

        $user->delete();

        return response()->json(['message' => 'User berhasil dihapus.']);
    }

    public function migrate()
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

            $message = $output ?: 'Migrasi gagal dijalankan.';

            return back()->with('error', $message);
        } catch (\Throwable $e) {
            Log::error('Manual migration failed.', ['message' => $e->getMessage()]);

            return back()->with('error', 'Terjadi kesalahan saat menjalankan migrasi: ' . $e->getMessage());
        }
    }
}
