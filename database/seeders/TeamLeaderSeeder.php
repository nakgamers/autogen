<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

/**
 * Seeder akun Team Leader.
 * Jalankan: php artisan db:seed --class=TeamLeaderSeeder
 */
class TeamLeaderSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'staf@bankjakarta.co.id'],
            [
                'name'     => 'Taat Tusriadi (Staf Restrukturisasi)',
                'password' => Hash::make('staf123'),
                'role'     => 'team_leader',
            ]
        );
    }
}
