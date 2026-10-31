<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Faker\Factory as Faker;

class GenerateDummyBankAccounts extends Command
{
    protected $signature = 'generate:dummy-bank-accounts {count=50000}';
    protected $description = 'Generate file CSV berisi data dummy bank accounts, lengkap dengan variasi data valid dan invalid untuk testing';

    public function handle()
    {
        $count = $this->argument('count');
        $faker = Faker::create('id_ID'); // Format Indonesia
        
        $fileName = 'dummy_bank_accounts_' . $count . '.csv';
        $filePath = storage_path('app/' . $fileName);
        
        $file = fopen($filePath, 'w');
        
        // Tulis Header (sesuai dengan heading row pada BankAccountImport.php)
        fputcsv($file, [
            'NO REKENING', 'NAMA NASABAH', 'NOA SAAT INI', 'SEGMEN', 'RM PENGELOLA', 
            'CABANG', 'KC CABANG', 'BANK', 'TUNGGAKAN POKOK', 'TUNGGAKAN BUNGA', 
            'DENDA', 'ALAMAT TAGIH', 'KOTA ALAMAT TAGIH', 'AGUNAN FA', 'RINCIAN AGUNAN', 
            'KELURAHAN AGUNAN', 'KECAMATAN AGUNAN', 'KOTA AGUNAN', 'PROVINSI AGUNAN', 'KETERANGAN'
        ]);

        $this->info("Mulai men-generate $count data (termasuk 5% data invalid acak)...");
        $bar = $this->output->createProgressBar($count);

        $segments = ['MIKRO', 'KREDIT KEPEMILIKAN PROPERTY', 'KREDIT MULTIGUNA', 'UKM', 'KREDIT KOMERSIAL II'];
        $banks = ['konvensional', 'syariah'];
        $agunans = ['beragunan', 'tidak beragunan'];

        // Daftar resmi Relationship Manager (RM) yang dikenali sistem
        $registeredManagers = [
            'Taat Tusriadi', 'Fajri Syahiffadhillah', 'Iksan Nurdin', 'Yopiardet Afridonyus',
            'Rino Marianto', 'Boby Megandy', 'Saor Hamonangan P', 'Imaddudin',
            'Dwi Rama Joko Prasetyo', 'Khoiril Anwar', 'Danu Dwi Riyanto', 'Yana Supriatna',
            'Andi Komara', 'Rengga Fradana', 'Hardian Sugito', 'Demas Brahmanto Putro',
            'Muhammad Taufan', 'Rizky Adjie Rachmadi', 'Sqatya Wibrahma', 'M.Suhendi Saputra',
            'Muhammad Ramli', 'Moehammad Anugerah P', 'Dian Evendi'
        ];

        for ($i = 0; $i < $count; $i++) {
            // 5% kemungkinan baris ini sengaja dibuat invalid untuk testing error handling
            $isInvalidRow = $faker->boolean(5); 

            if ($isInvalidRow) {
                $errorType = $faker->numberBetween(1, 3);
                
                $noRekening = match($errorType) {
                    1 => '', // Kosong (melanggar aturan required)
                    2 => 'ABCDE99999', // Mengandung huruf (melanggar aturan numeric)
                    3 => '123', // Terlalu pendek
                };
                
                $bank = 'BANK_GAGAL_VALIDASI'; // Melanggar Rule::in bank
                $segmen = ''; // Kosong
                $namaNasabah = ''; // Kosong
            } else {
                // Data normal / valid
                $noRekening = $faker->numberBetween(1000000000, 9999999999);
                $bank = $faker->randomElement($banks);
                $segmen = $faker->randomElement($segments);
                $namaNasabah = $faker->name;
            }
            
            // Nominal desimal
            $tunggakanPokok = $faker->boolean(70) ? 0 : $faker->randomFloat(2, 100000, 10000000);
            $tunggakanBunga = $tunggakanPokok > 0 ? round($tunggakanPokok * 0.1, 2) : 0;
            $denda = $tunggakanPokok > 0 ? $faker->randomFloat(2, 0, 500000) : 0;

            fputcsv($file, [
                $noRekening,
                $namaNasabah,
                $faker->numberBetween(1, 3),
                $segmen,
                $faker->randomElement($registeredManagers),
                'KCP ' . $faker->citySuffix,
                'KCU ' . $faker->city,
                $bank,
                $tunggakanPokok,
                $tunggakanBunga,
                $denda,
                $faker->streetAddress,
                $faker->city,
                $faker->randomElement($agunans),
                'Sertifikat / BPKB Dummy',
                $faker->citySuffix,
                $faker->citySuffix,
                $faker->city,
                $faker->state,
                $faker->sentence(3)
            ]);

            $bar->advance();
        }

        fclose($file);
        $bar->finish();
        $this->newLine();
        
        $this->info("Selesai! File CSV berhasil dibuat di: " . $filePath);
    }
}