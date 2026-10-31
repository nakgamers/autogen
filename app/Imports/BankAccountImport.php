<?php

namespace App\Imports;

use App\Models\BankAccount;
use App\Models\RmManager;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rule;
use Illuminate\Contracts\Queue\ShouldQueue; 
use Illuminate\Support\Facades\Cache; 

use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithUpserts;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Maatwebsite\Excel\Events\BeforeImport;
use Maatwebsite\Excel\Events\AfterImport;
use Maatwebsite\Excel\Validators\Failure;

class BankAccountImport implements 
    ToModel, 
    WithHeadingRow, 
    WithValidation, 
    WithChunkReading, 
    WithBatchInserts, 
    WithUpserts, 
    ShouldQueue, 
    WithEvents, 
    SkipsOnFailure, 
    SkipsOnError
{
    use SkipsFailures, SkipsErrors;

    public $batchId;
    protected array $rmCache = [];

    public function __construct($batchId = null)
    {
        $this->batchId = $batchId;
    }

    protected function getRmIdByName(string $name): ?int
    {
        if (isset($this->rmCache[$name])) {
            return $this->rmCache[$name];
        }

        $rm = RmManager::where('name', $name)->first();
        $this->rmCache[$name] = $rm ? $rm->id : null;

        return $this->rmCache[$name];
    }

    public function prepareForValidation($data, $index)
    {
        $cleanData = [];
        foreach ($data as $key => $value) {
            $cleanKey = trim(str_replace("\xEF\xBB\xBF", '', $key));
            $cleanData[$cleanKey] = $value;
        }
        $data = $cleanData;

        if (isset($data['no_rekening'])) {
            $data['no_rekening'] = preg_replace('/[^0-9]/', '', (string)$data['no_rekening']);
        }

        $data['noa_saat_ini'] = !empty($data['noa_saat_ini']) ? (int)$data['noa_saat_ini'] : 1;

        $cleanCurrency = function($value) {
            if ($value === null || $value === '') {
                return 0.0;
            }
            if (is_string($value)) {
                $value = str_replace(['Rp', 'rp', '.', ',', ' '], '', $value);
            }
            return (float) ($value ?? 0);
        };

        $data['tunggakan_pokok'] = $cleanCurrency($data['tunggakan_pokok'] ?? 0);
        $data['tunggakan_bunga'] = $cleanCurrency($data['tunggakan_bunga'] ?? 0);
        $data['denda'] = $cleanCurrency($data['denda'] ?? 0);

        return $data;
    }

    public function uniqueBy(): string|array
    {
        return 'account_number';
    }

    public function model(array $row): ?Model
    {
        if ($this->batchId) {
            Cache::increment("import_processed_{$this->batchId}");
            Cache::increment("import_success_{$this->batchId}");
        }

        $rmNameInput = trim($row['rm_pengelola'] ?? $row['rm_manager'] ?? $row['nama_rm'] ?? '');
        $rmManagerId = null;
        
        if (!empty($rmNameInput)) {
            $nameLower = strtolower($rmNameInput);
            
            $matchedName = match (true) {
                str_contains($nameLower, 'taat') => 'Taat Tusriadi',
                str_contains($nameLower, 'fajri') => 'Fajri Syahiffadhillah',
                str_contains($nameLower, 'iksan') => 'Iksan Nurdin',
                str_contains($nameLower, 'yopi') => 'Yopiardet Afridonyus',
                str_contains($nameLower, 'rino') => 'Rino Marianto',
                str_contains($nameLower, 'boby') => 'Boby Megandy',
                str_contains($nameLower, 'monang') => 'Saor Hamonangan P',
                str_contains($nameLower, 'imad') => 'Imaddudin',
                str_contains($nameLower, 'rama') => 'Dwi Rama Joko Prasetyo',
                str_contains($nameLower, 'khoiril') => 'Khoiril Anwar',
                str_contains($nameLower, 'danu') => 'Danu Dwi Riyanto',
                str_contains($nameLower, 'yana') => 'Yana Supriatna',
                str_contains($nameLower, 'andi') => 'Andi Komara',
                str_contains($nameLower, 'rengga') => 'Rengga Fradana',
                str_contains($nameLower, 'hardian') => 'Hardian Sugito',
                str_contains($nameLower, 'demas') => 'Demas Brahmanto Putro',
                str_contains($nameLower, 'taufan') => 'Muhammad Taufan',
                str_contains($nameLower, 'adjie') => 'Rizky Adjie Rachmadi',
                str_contains($nameLower, 'sqatya') => 'Sqatya Wibrahma',
                str_contains($nameLower, 'hendi') => 'M.Suhendi Saputra',
                str_contains($nameLower, 'ramli') => 'Muhammad Ramli',
                str_contains($nameLower, 'anugerah') => 'Moehammad Anugerah P',
                str_contains($nameLower, 'dian') => 'Dian Evendi',
                default => null,
            };

            if ($matchedName) {
                $rmManagerId = $this->getRmIdByName($matchedName);
            }
        }

        return new BankAccount([
            'account_number'        => $row['no_rekening'],
            'customer_name'         => trim($row['nama_nasabah']),
            'current_noa'           => (int) ($row['noa_saat_ini'] ?? 1),
            'segment'               => trim($row['segmen']),
            'rm_manager_id'         => $rmManagerId, 
            'branch'           => trim($row['cabang'] ?? ''),
            'kcu_branch_name'       => trim($row['kc_cabang'] ?? ''),
            'bank_name'             => strtolower(trim($row['bank'])),
            'hb_period'             => trim($row['periode_hb'] ?? ''), // Diubah dari 'period' ke 'hb_period'
            'principal_arrears'     => $row['tunggakan_pokok'] ?? 0,
            'interest_arrears'      => $row['tunggakan_bunga'] ?? 0,
            'penalty_fee'           => $row['denda'] ?? 0,
            'billing_address'       => trim($row['alamat_tagih'] ?? ''),
            'billing_city'          => trim($row['kota_alamat_tagih'] ?? ''),
            'fa_collateral'         => strtolower(trim($row['agunan_fa'] ?? '')),
            'collateral_details'    => trim($row['rincian_agunan'] ?? ''),
            'collateral_village'    => trim($row['kelurahan_agunan'] ?? ''),
            'collateral_district'   => trim($row['kecamatan_agunan'] ?? ''),
            'collateral_city'       => trim($row['kota_agunan'] ?? ''),
            'collateral_province'   => trim($row['provinsi_agunan'] ?? ''),
            'notes'                 => trim($row['keterangan'] ?? ''),
        ]);
    }

    public function rules(): array
    {
        return [
            'no_rekening' => ['required', 'numeric'],
            'nama_nasabah' => ['required', 'string'],
            'noa_saat_ini' => ['nullable', 'integer', 'min:1'],
            'segmen' => ['required', 'string'],
            'bank' => ['required', Rule::in(['KONVENSIONAL', 'SYARIAH', 'konvensional', 'syariah'])],
            'tunggakan_pokok' => ['nullable', 'numeric'],
            'tunggakan_bunga' => ['nullable', 'numeric'],
            'denda' => ['nullable', 'numeric'],
        ];
    }

    public function customValidationMessages(): array
    {
        return [
            'no_rekening.required' => 'Nomor rekening wajib diisi.',
            'no_rekening.numeric'  => 'Nomor rekening harus berupa angka.',
            'nama_nasabah.required'=> 'Nama nasabah tidak boleh kosong.',
            'segmen.required'      => 'Segmen wajib diisi.',
            'bank.required'        => 'Bank wajib diisi.',
        ];
    }

    public function onFailure(Failure ...$failures): void
    {
        if (!$this->batchId) return;

        foreach ($failures as $failure) {
            Cache::increment("import_processed_{$this->batchId}");
            Cache::increment("import_failed_{$this->batchId}");

            $errors = Cache::get("import_errors_{$this->batchId}", []);
            if (count($errors) < 100) { 
                $errors[] = [
                    'row' => $failure->row(), 
                    'message' => implode(', ', $failure->errors())
                ];
                Cache::put("import_errors_{$this->batchId}", $errors, now()->addHours(2));
            }
        }
    }

    public function onError(\Throwable $e): void
    {
        if (!$this->batchId) return;

        Cache::increment("import_processed_{$this->batchId}");
        Cache::increment("import_failed_{$this->batchId}");
        
        $errors = Cache::get("import_errors_{$this->batchId}", []);
        if (count($errors) < 100) {
            $errors[] = ['row' => 'Sistem', 'message' => 'Error DB: ' . $e->getMessage()];
            Cache::put("import_errors_{$this->batchId}", $errors, now()->addHours(2));
        }
    }

    public function registerEvents(): array
    {
        return [
            BeforeImport::class => function (BeforeImport $event) {
                if ($this->batchId) {
                    $totalRowsData = $event->reader->getTotalRows();
                    $totalRows = !empty($totalRowsData) ? (array_values($totalRowsData)[0] ?? 0) : 0;
                    Cache::put("import_total_{$this->batchId}", max(0, $totalRows), now()->addHours(2)); // Diperbaiki dari error syntax baris baru
                }
            },
            AfterImport::class => function (AfterImport$event) {
                if ($this->batchId) {
                    Cache::put("import_finished_{$this->batchId}", true, now()->addHours(2));
                }
            }
        ];
    }

    public function chunkSize(): int
    {
        return 250;
    }

    public function batchSize(): int
    {
        return 250;
    }
}