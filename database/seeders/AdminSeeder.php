<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        $password = env('ADMIN_PASSWORD');
        $user = User::firstOrNew(['email' => env('ADMIN_EMAIL', 'admin@example.com')]);
        $user->name     = $user->name ?: 'Administrator';
        $user->password = Hash::make($password);
        $user->role     = User::SUPER_ADMIN;
        $user->aktif    = 1;
        $user->save();

        if (blank($password)) {
            $this->command->error('Isi ADMIN_PASSWORD di file .env terlebih dahulu.');
            return;
        }

        User::updateOrCreate(
            ['email' => env('ADMIN_EMAIL', 'admin@example.com')],
            ['name' => 'Administrator', 'password' => Hash::make($password)]
        );
    }
}