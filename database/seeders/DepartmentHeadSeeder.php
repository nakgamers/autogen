<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

/**
 * Seeder akun Department Head.
 * Jalankan: php artisan db:seed --class=DepartmentHeadSeeder
 */
class DepartmentHeadSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'yogie@bankjakarta.co.id'],
            [
                'name'     => 'M Yogie Suryo Swasono (Pemimpin)',
                'password' => Hash::make('yogie123'),
                'role'     => 'department_head',
            ]
        );
    }
}
