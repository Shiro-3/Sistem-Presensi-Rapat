<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class BuatAdmin extends Command
{
    protected $signature = 'admin:buat {email : Email untuk login}
                            {--nama=Administrator : Nama tampilan (akun baru)}
                            {--peran= : super_admin atau admin (bawaan: admin untuk akun baru, tidak berubah untuk akun lama)}';

    protected $description = 'Buat akun, atau atur ulang password dan aktifkan kembali akun yang sudah ada';

    public function handle(): int
    {
        $email = Str::lower($this->argument('email'));
        $peran = $this->option('peran');

        if ($peran && ! array_key_exists($peran, User::PERAN)) {
            $this->error('Peran harus super_admin atau admin.');
            return self::FAILURE;
        }

        $password   = $this->secret('Password (minimal 10 karakter, huruf besar, huruf kecil, angka)');
        $konfirmasi = $this->secret('Ulangi password');

        $validator = Validator::make(
            ['email' => $email, 'password' => $password, 'password_confirmation' => $konfirmasi],
            [
                'email'    => ['required', 'email'],
                'password' => ['required', 'confirmed', 'min:10', 'regex:/[a-z]/', 'regex:/[A-Z]/', 'regex:/[0-9]/'],
            ],
            [
                'email.email'        => 'Format email tidak valid.',
                'password.confirmed' => 'Konfirmasi password tidak cocok.',
                'password.min'       => 'Password minimal :min karakter.',
                'password.regex'     => 'Password harus memuat huruf besar, huruf kecil, dan angka.',
            ]
        );

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $pesan) {
                $this->error($pesan);
            }
            return self::FAILURE;
        }

        $user = User::firstOrNew(['email' => $email]);
        $baru = ! $user->exists;

        if ($baru) {
            $user->name = $this->option('nama');
            $user->role = $peran ?: User::ADMIN;
        } elseif ($peran) {
            $user->role = $peran;
        }

        $user->password             = Hash::make($password);
        $user->aktif                = 1;
        $user->wajib_ganti_password = 0;
        $user->remember_token       = Str::random(60);
        $user->save();

        if (! $baru && config('session.driver') === 'database') {
            DB::table(config('session.table', 'sessions'))->where('user_id', $user->id)->delete();
        }

        $this->info(($baru ? 'Akun dibuat: ' : 'Akun diperbarui: ') . "{$email} ({$user->labelPeran()})");

        return self::SUCCESS;
    }
}