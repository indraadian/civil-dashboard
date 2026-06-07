<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ValidateRole
{
 public function handle(Request $request, Closure $next, ...$roles)
    {
        // Gunakan Auth::check()
        if (!Auth::check()) {
            return redirect('login');
        }

        // Gunakan Auth::user() untuk mengambil data user
        if (in_array(Auth::user()->role, $roles)) {
            return $next($request);
        }

        return abort(403, 'Unauthorized action.');
    }
}