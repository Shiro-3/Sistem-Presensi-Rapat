<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AkunController extends Controller
{
    public function edit(): View
    {
        return view('admin.password');
    }

    public function update(Request $request): RedirectResponse
    {
        $request->validate([
            'password_lama' => ['required', 'current_password'],
            'password'      => [
                'required', 'confirmed', 'different:password_lama',
                'min:10', 'regex:/[a-z]/', 'regex:/[A-Z]/', 'regex:/[0-9]/',
            ],
        ], [
            'password_lama.required'         => 'Password saat ini wajib diisi.',
            'password_lama.current_password' => 'Password saat ini salah.',
            'password.required'              => 'Password baru wajib diisi.',
            'password.confirmed'             => 'Konfirmasi password baru tidak cocok.',
            'password.different'             => 'Password baru harus berbeda dari password saat ini.',
            'password.min'                   => 'Password baru minimal :min karakter.',
            'password.regex'                 => 'Password baru harus memuat huruf besar, huruf kecil, dan angka.',
        ]);

        $user = $request->user();
        $wajibGanti = (bool) $user->wajib_ganti_password;

        $user->forceFill([
            'password'             => Hash::make($request->input('password')),
            'remember_token'       => Str::random(60),
            'wajib_ganti_password' => 0,
        ])->save();

        // Keluarkan perangkat lain yang masih login dengan akun ini, pertahankan sesi sekarang
        if (config('session.driver') === 'database') {
            DB::table(config('session.table', 'sessions'))
                ->where('user_id', $user->id)
                ->where('id', '!=', $request->session()->getId())
                ->delete();
        }

        $request->session()->regenerate();

        return redirect()
        ->route($wajibGanti ? 'admin.dashboard' : 'admin.password.edit')
        ->with('success', 'Password berhasil diubah.');
    }
}