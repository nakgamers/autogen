<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Maatwebsite\Excel\Facades\Excel;

class ConvertExcelToCsv extends Command
{
    protected $signature = 'excel:to-csv {filename : Nama file di dalam folder storage/app}';
    protected $description = 'Mengubah file Excel (.xlsx/.xls) menjadi format .csv secara bersih';

    public function handle()
    {
        $filename = $this->argument('filename');
        $path = storage_path('app/' . $filename);

        if (!file_exists($path)) {
            $this->error("File tidak ditemukan di: {$path}");
            return;
        }

        $this->info("Sedang memproses konversi file...");

        // Langsung baca file Excel ke array
        $data = Excel::toArray([], $path);

        if (empty($data) || empty($data[0])) {
            $this->error("File Excel kosong atau tidak bisa dibaca.");
            return;
        }

        // Ambil sheet pertama
        $rows = $data[0];

        // Tentukan nama file output CSV
        $csvFilename = pathinfo($filename, PATHINFO_FILENAME) . '.csv';
        $csvPath = storage_path('app/' . $csvFilename);

        // Buka file untuk ditulis dalam format CSV
        $file = fopen($csvPath, 'w');

        foreach ($rows as $row) {
            fputcsv($file, array_values($row));
        }

        fclose($file);

        $this->info("Berhasil! File CSV tersimpan di: " . $csvPath);
    }
}