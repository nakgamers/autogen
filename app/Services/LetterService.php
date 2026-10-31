<?php

namespace App\Services;

use App\Models\LetterType;
use App\Models\LetterTemplate;
use App\Models\BankAccount;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Auth;

class LetterService
{
    /**
     * Menyiapkan seluruh data yang dibutuhkan untuk template (termasuk kalkulasi)
     * Menggantikan tag @php yang panjang di dalam View/Template
     */
    public function prepareDataForTemplate($letter): array
    {
        $template = LetterTemplate::where('letter_type_id', $letter->letter_type_id)->firstOrFail();

        // Ambil account_number dari berbagai kemungkinan struktur data request / database
        $accountNumber = $letter->content_data['account_number'] 
            ?? ($letter->content_data['custom_data']['loan_account'] 
            ?? ($letter->content_data['custom_data']['account_number'] ?? null));

        $additionalAccountsInput = $letter->content_data['additional_accounts'] ?? [];
        $customData = $letter->content_data['custom_data'] ?? [];

        // Inisialisasi variabel tambahan khusus FPBK, PPJ, MMO, dan kustom
        $bundlesList  = [];
        $debtorsList  = [];
        $namaPemohon  = Auth::user()->name;
        $sppkNumber   = $customData['sppk_number'] ?? ($letter->content_data['sppk_number'] ?? '[No SPPK]');
        $sppkDate     = $customData['sppk_date'] ?? ($letter->content_data['sppk_date'] ?? '[Tanggal SPPK]');
        $deadlineDate = $customData['deadline_date'] ?? ($letter->content_data['deadline_date'] ?? date('Y-m-d', strtotime('+7 days')));
        $collateral   = '-';

        // Penanganan pilihan SAM unit untuk PPJ & MMO (SAM 1: Yenni Nainggolan, SAM 2: Muslim)
        $samUnit = $customData['pinbuk_sam_unit'] 
        ?? ($customData['ppj_sam_unit'] 
        ?? ($customData['sam_unit'] 
        ?? ($letter->content_data['sam_unit'] 
        ?? ($letter->content_data['custom_data']['pinbuk_sam_unit'] ?? ''))));
        $samUnit = strtoupper(trim($samUnit));
        $departmentHeadName = ($samUnit === 'SAM 2') ? 'Muslim' : 'Yenni Nainggolan';
        $departmentName     = ($samUnit === 'SAM 2') ? 'SAM 2 Write-Off Loan Resolution Department' : 'SAM 1 Write-Off Loan Resolution Department';

        if ($letter->letterType->code === 'FPBK') {
            $bundlesList = $letter->content_data['bundlesList'] ?? [];
            $namaPemohon = $letter->content_data['custom_data']['namaPemohon'] ?? Auth::user()->name;
        }

        // Penanganan khusus untuk Memo Biaya (MMO)
        if ($letter->letterType->code === 'MMO') {
            $debtorsList        = $letter->content_data['debtorsList'] ?? [];
            $departmentHeadName = $letter->content_data['departmentHeadName'] ?? $departmentHeadName;
            $departmentName     = $letter->content_data['sender_department'] ?? $departmentName;
        }

       // Variabel khusus PINBUK
       $customerName     = $customData['customer_name'] ?? ($letter->content_data['customer_name'] ?? '');
       $depositorName    = $customData['depositor_name'] ?? $customerName;
       $debitAccount     = $customData['debit_account'] ?? '91392217027';
       $debitAccountName = $customData['debit_account_name'] ?? 'Setoran Angsuran Krdt Konsumtip NKUK';
       $loanAccount      = $accountNumber ?? '-';
       $branchVal        = $customData['branch'] ?? '-';
       $hbPeriodVal      = $customData['hb_period'] ?? '-';
       $depositAmount    = $customData['deposit_amount'] ?? 0;
       $depositDate      = $customData['deposit_date'] ?? $letter->letter_date;
       $debitPurpose     = $customData['debit_purpose'] ?? 'Setoran pokok kredit';
       $slikUpdateNote   = $customData['slik_update_note'] ?? 'Mohon di update sesuai baki debet seharusnya';
       $pinbuk_description   = $customData['pinbuk_description'] ?? '-';
        
        // Variabel khusus PINBUK-BB (Memo Buka Blokir & Blokir Rekening Tabungan)
        if (!empty($customData['pinbuk_bb_sam_unit'])) {
            $bbSamUnit = strtoupper(trim($customData['pinbuk_bb_sam_unit']));
            $departmentHeadName = ($bbSamUnit === 'SAM 2') ? 'Muslim' : 'Yenni Nainggolan';
            $departmentName     = ($bbSamUnit === 'SAM 2') ? 'SAM 2 Write-Off Loan Resolution Department' : 'SAM 1 Write-Off Loan Resolution Department';
        }

        $decodeBbRows = function ($raw) {
            if (is_string($raw)) {
                $decoded = json_decode($raw, true);
                $raw = is_array($decoded) ? $decoded : [];
            }
            if (!is_array($raw)) {
                return [];
            }
            $numericKeys = ['unblock_amount', 'principal', 'interest', 'penalty', 'total', 'block_amount'];
            $rows = [];
            foreach ($raw as $row) {
                if (!is_array($row)) {
                    continue;
                }
                foreach ($numericKeys as $key) {
                    if (isset($row[$key])) {
                        $row[$key] = is_numeric($row[$key]) ? (float) $row[$key] : 0;
                    }
                }
                $rows[] = $row;
            }
            return $rows;
        };
        $tb1Rows = $decodeBbRows($customData['table_buka_blokir'] ?? []);
        $tb2Rows = $decodeBbRows($customData['table_blokir_rekening'] ?? []);

        // 1. Ambil Rekening Utama & RM Manager
        $bankAccount = BankAccount::with('rmManager')->where('account_number', $accountNumber)->first();
        if (!$bankAccount) {
            $bankAccount = new BankAccount(['account_number' => $accountNumber]);
        }

        $collateral = $bankAccount->collateral_details ?? ($customData['collateral_details'] ?? '-');

        // 2. Timpa Rekening Utama dengan data custom
        if (!empty($customData)) {
            $bankAccount->customer_name     = $customData['customer_name'] ?? $bankAccount->customer_name;
            $bankAccount->principal_arrears = $customData['principal_arrears'] ?? $bankAccount->principal_arrears;
            $bankAccount->interest_arrears  = $customData['interest_arrears'] ?? $bankAccount->interest_arrears;
            $bankAccount->penalty_fee       = $customData['penalty_fee'] ?? $bankAccount->penalty_fee;
            $bankAccount->segment           = $customData['segment'] ?? $bankAccount->segment;
            $bankAccount->bank_name         = $customData['bank_name'] ?? $bankAccount->bank_name;
        }

        $rmName = $customData['rm_name'] ?? ($letter->content_data['rm_name'] ?? $bankAccount?->rmManager?->name ?? '-');
        $rmPhone = $customData['rm_phone'] ?? ($letter->content_data['rm_phone'] ?? $bankAccount?->rmManager?->phone_number ?? '-');

        // 3. Tangani Rekening Tambahan (Ganda)
        $accountsList = collect([$bankAccount]);
        $additionalAccountsList = collect();

        if (!empty($additionalAccountsInput)) {
            $extraAccountNumbers = array_keys($additionalAccountsInput);
            $extraAccountsFromDb = BankAccount::whereIn('account_number', $extraAccountNumbers)->get();

            foreach ($extraAccountsFromDb as $extraAcc) {
                $editedData = $additionalAccountsInput[$extraAcc->account_number] ?? [];

                $extraAcc->principal_arrears = $editedData['principal_arrears'] ?? $extraAcc->principal_arrears;
                $extraAcc->interest_arrears  = $editedData['interest_arrears'] ?? $extraAcc->interest_arrears;
                $extraAcc->penalty_fee       = $editedData['penalty_fee'] ?? $extraAcc->penalty_fee;

                $additionalAccountsList->push($extraAcc);
                $accountsList->push($extraAcc);
            }
        }

        // 4. Kalkulasi Total
        $totalPokok = $accountsList->sum('principal_arrears');
        $totalBunga = $accountsList->sum('interest_arrears');
        $totalDenda = $accountsList->sum('penalty_fee');
        $totalAccumulated = $totalPokok + $totalBunga + $totalDenda;
        
        $allAccountNumbers = $accountsList->pluck('account_number')->toArray();

        // 5. Terminologi Berdasarkan Jenis Bank
        $isKonven = strtolower($bankAccount->bank_name ?? '') === 'konvensional';
        $termFasilitas = $isKonven ? 'kredit' : 'pembiayaan';

        return [
            'template' => $template,
            'data' => [
                'letter'                 => $letter,
                'letter_number'          => $letter->letter_number,
                'letter_date'            => $letter->letter_date,
                'due_date'               => $letter->content_data['due_date'] ?? null,
                'bankAccount'            => $bankAccount,
                'customer_name'          => $bankAccount->customer_name,
                'account_number'         => $bankAccount->account_number,
                'namaRM'                 => $rmName,
                'phoneRM'                => $rmPhone,
                'accountsList'           => $accountsList,
                'additionalAccountsList' => $additionalAccountsList,
                'allAccountNumbers'      => $allAccountNumbers,
                'totalPokok'             => $totalPokok,
                'totalBunga'             => $totalBunga,
                'totalDenda'             => $totalDenda,
                'totalAccumulated'       => $totalAccumulated,
                'isKonven'               => $isKonven,
                'termFasilitas'          => $termFasilitas,
                
                // Variabel khusus Memo Penyerahan Jaminan (PPJ)
                'sppk_number'            => $sppkNumber,
                'sppk_date'              => $sppkDate,
                'deadline_date'          => $deadlineDate,
                'collateral_details'     => $collateral,
                'departmentHeadName'     => $departmentHeadName,
                'departmentName'         => $departmentName,

                // PINBUK
                'depositor_name'     => $depositorName,
                'debit_account'      => $debitAccount,
                'debit_account_name' => $debitAccountName,
                'loan_account'       => $loanAccount,
                'branch'             => $branchVal,
                'hb_period'          => $hbPeriodVal,
                'deposit_amount'     => $depositAmount,
                'deposit_date'       => $depositDate,
                'debit_purpose'      => $debitPurpose,
                'slik_update_note'   => $slikUpdateNote,
                'pinbuk_description' => $pinbuk_description,
                // PINBUK-BB
                'tb1Rows'            => $tb1Rows,
                'tb2Rows'            => $tb2Rows,
                'table_buka_blokir'     => $customData['table_buka_blokir'] ?? null,
                'table_blokir_rekening' => $customData['table_blokir_rekening'] ?? null,

                // Variabel khusus Memo Biaya (MMO)
                'debtorsList'            => $debtorsList,
                'recipient_division'     => $letter->content_data['recipient_division'] ?? 'Strategy & Enabler Division Cq. Performance & Support Departement Head',
                'sender_department'      => $letter->content_data['sender_department'] ?? $departmentName,
                'subject_text'           => $letter->content_data['subject_text'] ?? 'Konfirmasi Biaya-Biaya Debitur',
                'inquiry_points'         => $letter->content_data['inquiry_points'] ?? [],

                // Variabel referensi SP (SP1, SP2, SP3)
                'previous_due_date'      => $letter->content_data['previous_due_date'] ?? ($customData['previous_due_date'] ?? null),
                'days_overdue'           => $letter->content_data['days_overdue'] ?? ($customData['days_overdue'] ?? 0),
                'previous_letter_number' => $letter->content_data['previous_letter_number'] ?? ($customData['previous_letter_number'] ?? null),
                'previous_letter_date'   => $letter->content_data['previous_letter_date'] ?? ($customData['previous_letter_date'] ?? null),
                
                // Variabel khusus Formulir Peminjaman Bundel (FPBK)
                'bundlesList'            => $bundlesList,
                'namaPemohon'            => $namaPemohon,
            ]
        ];
    }

    /**
     * Render isi template Blade string dengan data variabel
     */
    public function renderTemplate(LetterTemplate $template, array $data = []): string
    {
        return Blade::render($template->content, $data);
    }

    /**
     * Generate Nomor Surat Format Auto: 001/RPK/I/2026
     */
    public function generateLetterNumber(LetterType $type): string
    {
        $year = date('Y');
        $monthRoman = $this->getRomanMonth(date('n'));

        $lastLetterCount = \App\Models\Letter::whereYear('created_at', $year)->count() + 1;
        $sequence = str_pad($lastLetterCount, 3, '0', STR_PAD_LEFT);

        return "{$sequence}/{$type->code}/{$monthRoman}/{$year}";
    }

    private function getRomanMonth(int $month): string
    {
        $map = [
            1 => 'I', 2 => 'II', 3 => 'III', 4 => 'IV', 5 => 'V', 6 => 'VI',
            7 => 'VII', 8 => 'VIII', 9 => 'IX', 10 => 'X', 11 => 'XI', 12 => 'XII'
        ];
        return $map[$month] ?? 'I';
    }
}