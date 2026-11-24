<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class WajibGantiPassword
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->wajib_ganti_password && ! $request->routeIs('admin.password.*')) {
            return redirect()->route('admin.password.edit')
                ->with('peringatan', 'Anda harus mengganti password awal terlebih dahulu sebelum melanjutkan.');
        }

        return $next($request);
    }
}