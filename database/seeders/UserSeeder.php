<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * Wrapper lama — akun pengguna kini di-seed per role:
 * SuperAdminSeeder, TeamLeaderSeeder, DepartmentHeadSeeder.
 * Class ini dipertahankan agar perintah db:seed lama tetap berjalan.
 */
class UserSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            SuperAdminSeeder::class,
            TeamLeaderSeeder::class,
            DepartmentHeadSeeder::class,
        ]);
    }
}
