<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class AuthController extends Controller
{
    /**
     * Tampilkan halaman login.
     */
    public function showLogin(): View
    {
        return view('pages.auth.signin');
    }

    /**
     * Proses autentikasi login.
     */
    public function login(LoginRequest $request): RedirectResponse
    {
        if (Auth::attempt($request->validated())) {
            $request->session()->regenerate();

            return redirect()->intended('/civils');
        }

        return back()->withErrors([
            'email' => 'Email atau password salah.',
        ]);
    }

    /**
     * Logout dan invalidate session.
     */
    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }

    /**
     * Tampilkan halaman registrasi (dinonaktifkan).
     */
    public function showRegistrationForm(): RedirectResponse
    {
        return redirect()->route('login')
            ->with('info', 'Pendaftaran dibuka hanya melalui admin.');
    }

    /**
     * Proses registrasi user baru (dinonaktifkan).
     */
    public function register(): RedirectResponse
    {
        return redirect()->route('login')
            ->with('info', 'Pendaftaran mandiri dinonaktifkan. Silakan hubungi admin.');
    }
}
