<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Imports\BankAccountImport;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\BankAccount;
use Illuminate\Support\Facades\Cache;


class BankAccountController extends Controller
{ 

    public function showUploadForm()
    {
        return view('bank_accounts.upload');
    } 

    public function search(Request $request)
    {
        $search = $request->query('search');

        if (!$search) {
            return response()->json([]);
        }

        $accounts = BankAccount::with('rmManager')
            ->where('account_number', 'LIKE', "%{$search}%")
            ->orWhere('customer_name', 'LIKE', "%{$search}%")
            ->limit(50)
            ->get();

        $results = $accounts->map(function ($account) {
            return [
                'id'                => $account->account_number,
                'text'              => $account->account_number . ' - ' . $account->customer_name,
                'account_number'    => $account->account_number,
                'customer_name'     => $account->customer_name,
                'principal_arrears' => (float) $account->principal_arrears,
                'interest_arrears'  => (float) $account->interest_arrears,
                'penalty_fee'       => (float) $account->penalty_fee,
                'segment'           => $account->segment ?? $account->segmen ?? '-',
                'hb_period'         => $account->hb_period ?? '',       
                'branch'            => $account->branch ?? '',  
                'collateral_details'=> $account->collateral_details ?? '',
                'bank_name'         => $account->bank_name,
                'rm_name'           => $account->rmManager ? $account->rmManager->name : '',
                'rm_phone'          => $account->rmManager ? $account->rmManager->phone_number : '',
            ];
        });

        return response()->json($results);
    }

    public function checkDuplicate(Request $request)
    {
        $customerName = $request->query('customer_name');
        $excludeAccount = $request->query('exclude_account');

        if (!$customerName || !$excludeAccount) {
            return response()->json([]);
        }

        $duplicates = BankAccount::where('customer_name', $customerName)
            ->where('account_number', '!=', $excludeAccount)
            ->get();

        return response()->json($duplicates);
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => [
                'required',
                'file',
                'mimes:xlsx,xls,csv,txt', 
                'mimetypes:application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,application/vnd.ms-excel,text/csv,text/plain,application/octet-stream,application/zip',
                'max:51200'
            ],
        ]);

        try {
            Excel::import(new BankAccountImport, $request->file('file'));

            return redirect()->back()->with('success', 'Data Bank Account berhasil di-import / diperbarui!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    } 

    public function updateInline(Request $request)
    {
        $request->validate([
            'account_number' => 'required',
            'field'          => 'required|in:customer_name,segment,rm_name,bank_name,rm_phone', 
            'value'          => 'nullable|string'
        ]);

        try {
            $account = BankAccount::where('account_number', $request->account_number)->firstOrFail();

            if ($request->field === 'customer_name' || $request->field === 'segment') {
                $account->{$request->field} = $request->value;
                $account->save();
            } 
            elseif ($request->field === 'rm_name') {
                if ($account->rm_manager_id) {
                    $account->rmManager()->update(['name' => $request->value]);
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Data berhasil diupdate'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Memproses Ajax Import dengan Konversi Otomatis Excel (.xlsx/.xls) ke CSV
     */
    public function importAjax(Request $request)
{
    // Longgarkan limit memori dan waktu untuk mengantisipasi file Excel besar
    ini_set('memory_limit', '1024M');
    set_time_limit(600);

    $request->validate([
        'file' => 'required|mimes:xlsx,xls,csv,txt|max:51200',
    ]);

    try {
        $file = $request->file('file');
        $extension = strtolower($file->getClientOriginalExtension());
        $batchId = uniqid('import__');

        // Simpan file apa adanya (baik .xlsx, .xls, maupun .csv) ke storage/app/imports
        $fileName = $batchId . '.' . $extension;
        $importFile = $file->storeAs('imports', $fileName, 'local');

        // Inisialisasi Cache untuk progress bar
        Cache::put("import_total_{$batchId}", 0, now()->addHours(2));
        Cache::put("import_processed_{$batchId}", 0, now()->addHours(2));
        Cache::put("import_success_{$batchId}", 0, now()->addHours(2));
        Cache::put("import_failed_{$batchId}", 0, now()->addHours(2));
        Cache::put("import_errors_{$batchId}", [], now()->addHours(2));
        Cache::put("import_finished_{$batchId}", false, now()->addHours(2));

        // Eksekusi Job ke Background Queue (Laravel Excel otomatis menangani .xlsx, .xls, & .csv)
        Excel::queueImport(new BankAccountImport($batchId), $importFile, 'local');

        return response()->json([
            'success' => true,
            'batch_id' => $batchId,
            'message' => 'File berhasil diunggah dan masuk antrean import.'
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Gagal memproses file: ' . $e->getMessage()
        ], 500);
    }
}
    public function importStatus($batchId)
    {
        $total = Cache::get("import_total_{$batchId}", 0);
        $processed = Cache::get("import_processed_{$batchId}", 0);
        $success = Cache::get("import_success_{$batchId}", 0);
        $failed = Cache::get("import_failed_{$batchId}", 0);
        $errors = Cache::get("import_errors_{$batchId}", []);
        $isFinished = Cache::get("import_finished_{$batchId}", false);

        $progress = 0;
        if ($total > 0) {
            $progress = floor(($processed / $total) * 100);
            if ($progress > 100) $progress = 100;
        }

        return response()->json([
            'progress'    => $progress,
            'processed'   => $processed,
            'total'       => $total,
            'success'     => $success,
            'failed'      => $failed,
            'is_finished' => $isFinished,
            'errors'      => $errors
        ]);
    }
}