<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; // <-- Import Auth
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsSellerWithToko
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Pindahkan logika dari Closure di web.php ke sini
        $user = Auth::user();
        if (!$user || $user->role !== 'penjual' || !$user->toko) {
            // Jika bukan penjual atau tidak punya toko, tolak
            return redirect()->route('dashboard')->with('error', 'Aksi tidak diizinkan.');
        }

        // Jika lolos, lanjutkan request
        return $next($request);
    }
}
