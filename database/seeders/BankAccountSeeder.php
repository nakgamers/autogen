<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\BankAccount;
use App\Models\RmManager;

class BankAccountSeeder extends Seeder
{
    /**
     * Helper untuk mengambil ID RM Manager berdasarkan Nama Depan
     */
    private function getRmIdByName(string $firstName): ?int
    {
        return RmManager::where('name', 'LIKE', $firstName . '%')->value('id');
    }

    public function run(): void
    {
        // Pemetaan ID RM Pengelola berdasarkan Nama Depan
        $rmTaat                      = $this->getRmIdByName('Taat Tusriadi');
        $rmFajri                     = $this->getRmIdByName('Fajri Syahiffadhillah');
        $rmMonang                     = $this->getRmIdByName('Saor Hamonangan P');
        $rmImad                 = $this->getRmIdByName('Imaddudin');
        $rmAndi                      = $this->getRmIdByName('Andi Komara');
        

        $accounts = [
            // Data 1: Syariah - Mikro
            [
                'account_number'        => '9139221702',
                'customer_name'         => 'Lilis Komala Dewi',
                'current_noa'           => 1,
                'segment'               => 'MIKRO',
                'collateral_details'    => 'SHM No. 06952/Sunter Jaya an. Yusnani',
                'rm_manager_id'         => $rmTaat,
                'branch'                => 'KC Sumedang',
                'kcu_branch_name'       => 'KCU Bandung',
                'bank_name'             => 'syariah',
                'hb_period'             => 'September 2025',
                'principal_arrears'     => 45000000.00,
                'interest_arrears'      => 12500000.00,
                'penalty_fee'           => 1500000.00,
                'billing_address'       => 'Dusun Lembur Warung No.03 RT 01/RW 06',
                'billing_city'          => 'Kabupaten Sumedang',
                'fa_collateral'         => 'beragunan',
                'notes'                 => 'Kredit Bermasalah - Penanganan Restrukturisasi',
            ],

            // Data 2: Konvensional - KPR
            [
                'account_number'        => '9139221703',
                'customer_name'         => 'Nuropah',
                'current_noa'           => 1,
                'segment'               => 'KREDIT KEPEMILIKAN PROPERTY',
                'collateral_details'    => 'SHGB No. 1245/Muktiwari an. Nuropah',
                'rm_manager_id'         => $rmMonang,
                'branch'                => 'KC Bekasi',
                'kcu_branch_name'       => 'KCU Bekasi Utama',
                'bank_name'             => 'konvensional',
                'hb_period'             => 'September 2025',
                'principal_arrears'     => 8500000.00,
                'interest_arrears'      => 4200000.00,
                'penalty_fee'           => 750000.00,
                'billing_address'       => 'Logam Bangun Setia 2 Tahap 3 Blok J Muktiwari',
                'billing_city'          => 'Bekasi',
                'fa_collateral'         => 'beragunan',
                'notes'                 => 'Tunggakan Bunga & Denda',
            ],

            // Data 3: Konvensional - UKM
            [
                'account_number'        => '1029384756',
                'customer_name'         => 'PT Mulia Jaya Sejahtera',
                'current_noa'           => 2,
                'segment'               => 'UKM',
                'collateral_details'    => 'SHM No. 8831/Sudirman an. PT Mulia Jaya Sejahtera',
                'rm_manager_id'         => $rmFajri,
                'branch'                => 'KC Jakarta Sudirman',
                'kcu_branch_name'       => 'KCU Jakarta Utama',
                'bank_name'             => 'konvensional',
                'hb_period'             => 'September 2025',
                'principal_arrears'     => 150000000.00,
                'interest_arrears'      => 25000000.00,
                'penalty_fee'           => 5000000.00,
                'billing_address'       => 'Jl. Jend. Sudirman No. 45',
                'billing_city'          => 'Jakarta Selatan',
                'fa_collateral'         => 'beragunan',
                'notes'                 => 'Fasilitas Kredit Usaha Komersial',
            ],

            // Data 4: Syariah - KMG
            [
                'account_number'        => '8812345678',
                'customer_name'         => 'Hendra Setiawan',
                'current_noa'           => 1,
                'segment'               => 'KREDIT MULTIGUNA',
                'collateral_details'    => '-',
                'rm_manager_id'         => $rmImad,
                'branch'                => 'KC Bogor',
                'kcu_branch_name'       => 'KCU Bogor',
                'bank_name'             => 'syariah',
                'hb_period'             => 'September 2025',
                'principal_arrears'     => 5000000.00,
                'interest_arrears'      => 1500000.00,
                'penalty_fee'           => 0.00,
                'billing_address'       => 'Jl. Pajajaran No. 12 RT 02/RW 04',
                'billing_city'          => 'Kota Bogor',
                'fa_collateral'         => 'tidak beragunan',
                'notes'                 => 'Pembiayaan Multiguna Pegawai',
            ],

            // Data 5: Konvensional - Mikro
            [
                'account_number'        => '7765432109',
                'customer_name'         => 'Bambang Sudirdjo',
                'current_noa'           => 1,
                'segment'               => 'MIKRO',
                'collateral_details'    => 'SK Menegkop / BPKB Mobil Toyota Avanza an. Bambang Sudirdjo',
                'rm_manager_id'         => $rmAndi,
                'branch'                => 'KC Depok',
                'kcu_branch_name'       => 'KCU Margonda',
                'bank_name'             => 'konvensional',
                'hb_period'             => 'September 2025',
                'principal_arrears'     => 3500000.00,
                'interest_arrears'      => 850000.00,
                'penalty_fee'           => 150000.00,
                'billing_address'       => 'Jl. Margonda Raya No. 88',
                'billing_city'          => 'Kota Depok',
                'fa_collateral'         => 'beragunan',
                'notes'                 => 'Kredit Mikro Usaha Mandiri',
            ],
        ];

        foreach ($accounts as $account) {
            BankAccount::updateOrCreate(
                ['account_number' => $account['account_number']],
                $account
            );
        }
    }
}