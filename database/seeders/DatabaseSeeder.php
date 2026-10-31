<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            SuperAdminSeeder::class,     // 1a. Akun Super Admin
            TeamLeaderSeeder::class,     // 1b. Akun Team Leader
            DepartmentHeadSeeder::class, // 1c. Akun Department Head
            RmManagerSeeder::class,   // 2. Buat data RM Manager (Acuan untuk Bank Account)
            BankAccountSeeder::class, // 3. Buat Rekening Bank (Menggunakan ID dari RmManagerSeeder)
            MailSystemSeeder::class,    // 4. Buat Template Surat & Jenis Surat
        ]);
    }
}