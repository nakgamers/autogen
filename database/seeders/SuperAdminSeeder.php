<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

/**
 * Seeder akun Super Admin.
 * Jalankan: php artisan db:seed --class=SuperAdminSeeder
 */
class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@bankjakarta.co.id'],
            [
                'name'     => 'Administrator System',
                'password' => Hash::make('admin123'), // Ganti password sesuai kebutuhan
                'role'     => 'super_admin',
            ]
        );
    }
}
