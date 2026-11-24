<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PenggunaController extends Controller
{
    private const ATURAN_PASSWORD = ['required', 'string', 'min:10', 'regex:/[a-z]/', 'regex:/[A-Z]/', 'regex:/[0-9]/'];

    private const PESAN = [
        'name.required'     => 'Nama wajib diisi.',
        'email.required'    => 'Email wajib diisi.',
        'email.email'       => 'Format email tidak valid.',
        'email.unique'      => 'Email ini sudah terdaftar.',
        'role.required'     => 'Peran wajib dipilih.',
        'role.in'           => 'Peran tidak valid.',
        'password.required' => 'Password wajib diisi.',
        'password.min'      => 'Password minimal :min karakter.',
        'password.regex'    => 'Password harus memuat huruf besar, huruf kecil, dan angka.',
    ];

    public function index(): View
    {
        $penggunas = User::orderByDesc('role')->orderBy('name')->get();

        return view('admin.pengguna.index', compact('penggunas'));
    }

    public function create(): View
    {
        return view('admin.pengguna.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $this->normalkanEmail($request);

        $data = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'email', 'max:255', 'unique:users,email'],
            'role'     => ['required', Rule::in(array_keys(User::PERAN))],
            'password' => self::ATURAN_PASSWORD,
        ], self::PESAN);

        $user = new User([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'password' => $data['password'],
        ]);
        $user->role                 = $data['role'];
        $user->aktif                = 1;
        $user->wajib_ganti_password = 1; // password awal wajib diganti saat login pertama
        $user->save();

        return redirect()
            ->route('admin.pengguna.index')
            ->with('success', "Pengguna {$user->name} berhasil dibuat. Berikan password awalnya secara langsung; ia wajib menggantinya saat login pertama.");
    }

    public function edit(Request $request, User $pengguna): View
    {
        return view('admin.pengguna.edit', [
            'pengguna'    => $pengguna,
            'diriSendiri' => $request->user()->is($pengguna),
        ]);
    }

    public function update(Request $request, User $pengguna): RedirectResponse
    {
        $diriSendiri = $request->user()->is($pengguna);

        $this->normalkanEmail($request);

        $aturan = [
            'name'  => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($pengguna->id)],
        ];

        // Peran dan status akun sendiri tidak boleh diubah (menjaga selalu ada Super Admin aktif)
        if (! $diriSendiri) {
            $aturan['role']  = ['required', Rule::in(array_keys(User::PERAN))];
            $aturan['aktif'] = ['nullable', 'boolean'];
        }

        $data = $request->validate($aturan, self::PESAN);

        $pengguna->name  = $data['name'];
        $pengguna->email = $data['email'];

        $dinonaktifkan = false;

        if (! $diriSendiri) {
            $pengguna->role = $data['role'];
            $aktif = $request->boolean('aktif');
            $dinonaktifkan = $pengguna->aktif && ! $aktif;
            $pengguna->aktif = $aktif ? 1 : 0;
        }

        $pengguna->save();

        if ($dinonaktifkan) {
            $this->keluarkanSesi($pengguna);
        }

        return redirect()
            ->route('admin.pengguna.index')
            ->with('success', "Data {$pengguna->name} berhasil diperbarui.");
    }

    public function resetPassword(Request $request, User $pengguna): RedirectResponse
    {
        if ($request->user()->is($pengguna)) {
            return back()->withErrors(['password' => 'Gunakan menu Ubah Password untuk akun Anda sendiri.']);
        }

        $request->validate(['password' => self::ATURAN_PASSWORD], self::PESAN);

        $pengguna->forceFill([
            'password'             => Hash::make($request->input('password')),
            'wajib_ganti_password' => 1,
            'remember_token'       => Str::random(60),
        ])->save();

        $this->keluarkanSesi($pengguna);

        return redirect()
            ->route('admin.pengguna.edit', $pengguna)
            ->with('success', "Password {$pengguna->name} diatur ulang. Ia wajib menggantinya saat login berikutnya.");
    }

    private function normalkanEmail(Request $request): void
    {
        $request->merge(['email' => Str::lower(trim((string) $request->input('email')))]);
    }

    private function keluarkanSesi(User $user): void
    {
        if (config('session.driver') === 'database') {
            DB::table(config('session.table', 'sessions'))->where('user_id', $user->id)->delete();
        }
    }
}