<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('pages.auth.signin');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            return redirect()->intended('/civils');
        }

        return back()->withErrors([
            'email' => 'Email atau password salah.',
        ]);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/login');
    }

    public function showRegistrationForm()
    {
        return view('pages.auth.signup');
    }

    public function register(Request $request)
    {
        // 1. Validasi Input
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:6']
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'role' => 'user',
            'password' => Hash::make($request->password),
        ]);

        // Tambahkan baris ini untuk debug
        Auth::login($user);

        if (Auth::check()) {
            // Jika masuk ke sini, artinya login BERHASIL
            return redirect()->intended('/dashboard')->with('success', 'Akun berhasil dibuat!');
        } else {
            // Jika masuk ke sini, artinya ada masalah dengan session/auth guard
            dd("Login gagal, session tidak terbentuk.");
        }
        // 4. Redirect ke dashboard
        return redirect('/dashboard')->with('success', 'Akun berhasil dibuat!');
    }

    public function createAdminUser()
    {
        $user = User::create([
            'name' => 'indev',
            'email' => 'indev@indev.com',
            'role' => 'admin',
            'password' => Hash::make('123Indev'),
        ]);
        return view('pages.auth.signin');
    }
}
