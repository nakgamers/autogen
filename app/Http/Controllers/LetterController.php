<?php

namespace App\Http\Controllers;

use App\Models\Letter;
use App\Models\LetterType;
use App\Models\User;
use App\Models\BankAccount;
use App\Services\LetterService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

class LetterController extends Controller
{
    protected $letterService;

    // Inject LetterService ke dalam Controller
    public function __construct(LetterService $letterService)
    {
        $this->letterService = $letterService;
    }

    /**
     * Menampilkan daftar surat (Index)
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $query = Letter::with(['letterType', 'user']);

        if ($user->isTeamLeader()) {
            // Team Leader: hanya surat milik sendiri, 30 hari terakhir.
            $query->where('user_id', $user->id)
                ->where('created_at', '>=', Carbon::now()->subDays(30)->startOfDay());
        } elseif ($user->isDepartmentHead()) {
            // Department Head: riwayat semua Team Leader + filter.
            // Jendela 30 hari hanya bila tidak ada filter tanggal.
            if (! $request->filled('tanggal_dari') && ! $request->filled('tanggal_sampai')) {
                $query->where('created_at', '>=', Carbon::now()->subDays(30)->startOfDay());
            }
            if ($request->filled('team_leader')) {
                $query->where('user_id', $request->integer('team_leader'));
            }
            if ($request->filled('letter_type_id')) {
                $query->where('letter_type_id', $request->integer('letter_type_id'));
            }
            if ($request->filled('tanggal_dari')) {
                $query->whereDate('letter_date', '>=', $request->input('tanggal_dari'));
            }
            if ($request->filled('tanggal_sampai')) {
                $query->whereDate('letter_date', '<=', $request->input('tanggal_sampai'));
            }
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('letter_number', 'like', "%{$search}%")
                  ->orWhere('subject', 'like', "%{$search}%")
                  ->orWhere('recipient', 'like', "%{$search}%");
            });
        }

        $letters = $query->latest()->paginate(10)->withQueryString();

        $teamLeaders = $user->isDepartmentHead() ? User::where('role', 'team_leader')->orderBy('name')->get() : collect();
        $letterTypes = $user->isDepartmentHead() ? LetterType::orderBy('id')->get() : collect();

        return view('letters.index', compact('letters', 'teamLeaders', 'letterTypes'));
    }

    /**
     * Helper untuk membuat nomor surat secara global berdasarkan urutan tertinggi di bulan/tahun berjalan
     */
    private function generateLetterNumber($letterTypeId): string
    {
        $letterType = LetterType::findOrFail($letterTypeId);
        
        $currentYear = date('Y');
        $currentMonth = date('n');

        $romanMonths = [
            1 => 'I', 2 => 'II', 3 => 'III', 4 => 'IV', 5 => 'V', 6 => 'VI',
            7 => 'VII', 8 => 'VIII', 9 => 'IX', 10 => 'X', 11 => 'XI', 12 => 'XII'
        ];
        $romanMonth = $romanMonths[$currentMonth] ?? 'I';

        $format = $letterType->format_number ?? '{sequence}/WOLSAM/{month}/{year}';

        // Cari NOMOR URUT TERTINGGI secara mutlak dari SELURUH surat di bulan & tahun ini
        $maxSeq = Letter::whereYear('created_at', $currentYear)
            ->whereMonth('created_at', $currentMonth)
            ->get()
            ->max(function ($letter) {
                // Ambil angka pertama dari nomor surat (misal "001/WOLSAM/..." -> 1)
                if (preg_match('/^(\d+)/', $letter->letter_number, $matches)) {
                    return (int) $matches[1];
                }
                return 0;
            });

        $nextCount = ($maxSeq ?? 0) + 1;
        $sequence = str_pad($nextCount, 3, '0', STR_PAD_LEFT);

        return str_replace(
            ['{sequence}', '{month}', '{year}'],
            [$sequence, $romanMonth, $currentYear],
            $format
        );
    }

    /**
     * Menampilkan form pembuatan surat
     */
    public function create()
    {
        $letterTypes = LetterType::all();

        $bankAccounts = BankAccount::with(['rmManager' => function($query) {
                $query->select('id', 'name', 'phone_number');
            }])
            ->select('account_number', 'customer_name', 'principal_arrears', 'interest_arrears', 'penalty_fee', 'segment', 'bank_name', 'rm_manager_id', 'hb_period', 'branch')
            ->orderBy('customer_name', 'asc')
            ->get();

        $uniqueSegments = $bankAccounts->pluck('segment')->unique()->filter()->values();
        
        $uniqueBanks = $bankAccounts->pluck('bank_name')
            ->map(fn($bank) => strtoupper($bank))
            ->unique()
            ->filter()
            ->values();

        $uniqueRMs = $bankAccounts->map(function($acc) {
                return $acc->rmManager->name ?? 'Belum Ditentukan';
            })
            ->unique()
            ->filter()
            ->values();

        return view('letters.create', compact(
            'letterTypes', 
            'bankAccounts', 
            'uniqueSegments', 
            'uniqueBanks', 
            'uniqueRMs'
        ));
    }
    
    
    /**
     * Menyimpan data surat ke database
     */
    public function store(Request $request)
    {
        // Validasi umum semua jenis surat (gerbang sebelum surat dibuat)
        $request->validate([
            'letter_type_id' => 'required|exists:letter_types,id',
            'letter_date'    => 'nullable|date',
        ]);

        $letterType = LetterType::findOrFail($request->letter_type_id);
        $code = strtoupper(trim($letterType->code));

        // ==========================================
        // 1. MEMO BIAYA (MMO)
        // ==========================================
        if ($code === 'MMO') {
            $rawDebtors = $request->input('debtors', []);
            $cleanedDebtors = array_values(array_filter($rawDebtors, function($item) {
                return !empty($item['account_number']) && !empty($item['customer_name']);
            }));

            $request->merge(['debtors' => $cleanedDebtors]);

            $request->validate([
                'letter_type_id'          => 'required|exists:letter_types,id',
                'memo_target'             => 'required|in:support,auction',
                'sam_unit'                => 'required|in:SAM 1,SAM 2',
                'debtors'                 => 'required|array|min:1',
                'debtors.*.account_number'=> 'required|string',
                'debtors.*.customer_name' => 'required|string',
                'debtors.*.branch'        => 'nullable|string',
                'debtors.*.hb_period'     => 'nullable|string',
                'debtors.*.segment'       => 'nullable|string',
                'due_date'                => 'required|date',
                'letter_date'             => 'required|date',
            ]);

            $isSupport = $request->memo_target === 'support';
            
            $recipientDivision = $isSupport 
                ? 'Strategy & Enabler Division Cq. Performance & Support Departement Head'
                : 'Write-Off Loan & Auction Division Cq. Auction Departement Head';

            $inquiryPoints = $isSupport 
                ? [
                    'Rincian dana talangan',
                    'Subrogasi beserta nama perusahaan penjaminan'
                  ]
                : [
                    'Biaya pembatalan lelang',
                    'Biaya-biaya yang telah dikeluarkan termasuk biaya yang akan timbul jika debitur melakukan pelunasan',
                    'Jika sedang proses lelang, mohon sertakan tanggal pelaksanaan lelang'
                  ];

            // Tentukan Department Head dan Nama Departemen berdasarkan pilihan SAM 1 atau SAM 2
            $samUnit = $request->sam_unit;
            $senderDepartment = $samUnit === 'SAM 1' ? 'SAM 1 Write-Off Loan Resolution Department' : 'SAM 2 Write-Off Loan Resolution Department';
            $headName = $samUnit === 'SAM 1' ? 'Yenni Nainggolan' : 'Muslim';

            $letter = Letter::create([
                'letter_type_id' => $request->letter_type_id,
                'letter_number'  => $this->generateLetterNumber($request->letter_type_id),
                'letter_date'    => $request->letter_date,
                'user_id'        => Auth::id(),
                'recipient'      => $recipientDivision,
                'subject'        => 'Konfirmasi Biaya-Biaya Debitur',
                'content_data'   => [
                    'recipient_division' => $recipientDivision,
                    'sender_department'  => $senderDepartment,
                    'subject_text'       => 'Konfirmasi Biaya-Biaya Debitur',
                    'inquiry_points'     => $inquiryPoints,
                    'debtorsList'        => $request->debtors, // Memastikan rincian debitur terbaca di template
                    'due_date'           => $request->due_date,
                    'letter_date'        => $request->letter_date,
                    'departmentHeadName' => $headName,
                ],
            ]);

            return redirect()->route('letters.preview', $letter->id);
        }

        // ==========================================
        // 2. FPBK
        // ==========================================
        elseif ($code === 'FPBK') {
            $request->validate([
                'letter_type_id'                  => 'required|exists:letter_types,id',
                'bundle_main.account_number'      => 'required|string',
                'bundle_main.customer_name'       => 'required|string',
                'bundle_main.segment'             => 'required|string',
                'bundle_main.hb_period'           => 'required|string',
                'bundle_main.branch'              => 'required|string',
                'bundle_main.purpose'             => 'required|string',
                'additional_bundles'              => 'nullable|array|max:9',
                'additional_bundles.*.account_number' => 'required|string',
                'additional_bundles.*.customer_name'  => 'required|string',
                'additional_bundles.*.segment'        => 'required|string',
                'additional_bundles.*.hb_period'      => 'required|string',
                'additional_bundles.*.branch'         => 'required|string',
                'additional_bundles.*.purpose'        => 'required|string',
            ]);

            $bundlesList = [];
            $bundlesList[] = $request->input('bundle_main');

            if ($request->has('additional_bundles')) {
                foreach ($request->input('additional_bundles') as $addBundle) {
                    $bundlesList[] = $addBundle;
                }
            }

            $letter = Letter::create([
                'letter_type_id' => $request->letter_type_id,
                'letter_number'  => $this->generateLetterNumber($request->letter_type_id),
                'letter_date'    => $request->letter_date ?? now()->toDateString(),
                'user_id'        => Auth::id(),
                'recipient'      => $request->input('bundle_main.customer_name'),
                'subject'        => 'Formulir Peminjaman Bundel Kredit/Pembiayaan',
                'content_data'   => [
                    'bundlesList' => $bundlesList,
                    'custom_data' => [
                        'namaPemohon' => Auth::user()->name, 
                    ]
                ],
            ]);

            return redirect()->route('letters.preview', $letter->id);
        }

        // ==========================================
        // 3. PPJ
        // ==========================================
        elseif ($code === 'PPJ') {
            $request->validate([
                'letter_type_id'                => 'required|exists:letter_types,id',
                'account_number'                => 'required|string',
                'letter_date'                   => 'required|date',
                'custom_data.customer_name'     => 'required|string',
                'custom_data.collateral_details'=> 'required|string',
                'custom_data.sppk_number'       => 'required|string',
                'custom_data.sppk_date'         => 'required|date',
                'custom_data.deadline_date'     => 'required|date',
                'custom_data.ppj_sam_unit'          => 'required|in:SAM 1,SAM 2',
            ]);

            $customData = $request->input('custom_data');
            $customerName = $customData['customer_name'];

            $letter = Letter::create([
                'letter_type_id' => $request->letter_type_id,
                'letter_number'  => $this->generateLetterNumber($request->letter_type_id),
                'letter_date'    => $request->letter_date,
                'user_id'        => Auth::id(),
                'recipient'      => 'Strategy & Enabler Division Cq. Performance & Support Departement Head',
                'subject'        => 'Permintaan Penyerahan Jaminan dan Dokumen Pelunasan an. ' . $customerName,
                'content_data'   => [
                    'account_number' => $request->account_number,
                    'custom_data'    => $customData,
                ],
            ]);

            return redirect()->route('letters.preview', $letter->id);
        }

        // ==========================================
        // 4. PINBUK
        // ==========================================
        elseif ($code === 'PINBUK') {
            $request->validate([
                'letter_type_id'               => 'required|exists:letter_types,id',
                'account_number'               => 'required|string',
                'letter_date'                  => 'required|date',
                'custom_data.customer_name'    => 'nullable|string', 
                'custom_data.depositor_name'   => 'required|string',
                'custom_data.debit_account'    => 'required|string',
                'custom_data.debit_account_name' => 'required|string',
                'custom_data.branch'           => 'required|string',
                'custom_data.hb_period'        => 'required|string',
                'custom_data.deposit_amount'   => 'required|numeric',
                'custom_data.deposit_date'     => 'required|date',
                'custom_data.debit_purpose'    => 'required|string',
                'custom_data.slik_update_note' => 'required|string',
                'custom_data.pinbuk_sam_unit' => 'required|in:SAM 1,SAM 2',
                'custom_data.pinbuk_description' => 'required|string',
                'custom_data.pinbuk_table'       => 'nullable|string'
            ]);

            $customData = $request->input('custom_data');

            // PENGAMAN OTOMATIS: Jika customer_name dari form kosong, ambil otomatis dari BankAccount
            if (empty($customData['customer_name'])) {
                $bankAccount = BankAccount::where('account_number', $request->account_number)->first();
                $customData['customer_name'] = $bankAccount->customer_name ?? 'Nasabah';
            }

            $request->merge(['custom_data' => $customData]);

            $letter = Letter::create([
                'letter_type_id' => $request->letter_type_id,
                'letter_number'  => $this->generateLetterNumber($request->letter_type_id),
                'letter_date'    => $request->letter_date,
                'user_id'        => Auth::id(),
                'recipient'      => 'Strategy & Enabler Division Cq. Performance & Support Departement Head',
                'subject'        => 'Pemindah bukuan Setoran Kredit an. ' . $customData['customer_name'],
                'content_data'   => [
                    'account_number' => $request->account_number,
                    'custom_data'    => $customData,
                ],
            ]);

            return redirect()->route('letters.preview', $letter->id);
        }

        // ==========================================
        // 5. PENAGIHAN (SPKP, SP1, SP2, SP3)
        // ==========================================
        elseif (in_array($code, ['SPKP', 'SP1', 'SP2', 'SP3'])) {
            $request->validate([
                'letter_type_id'                        => 'required|exists:letter_types,id',
                'account_number'                        => 'required',
                'customer_name'                         => 'required|string',
                'principal_arrears'                     => 'required|numeric',
                'subrogation'                           => 'nullable|numeric|min:0',
                'interest_arrears'                      => 'required|numeric',
                'penalty_fee'                           => 'required|numeric',
                'segment'                               => 'nullable|string',
                'bank_name'                             => 'required|in:konvensional,syariah',
                'rm_name'                               => 'nullable|string',
                'rm_phone'                              => 'nullable|string',
                'due_date'                              => 'required|date',
                'previous_due_date'                     => 'nullable',
                'days_overdue'                          => 'nullable|numeric',
                'previous_letter_number'                => 'nullable|string',
                'previous_letter_date'                  => 'nullable|date',
                'additional_accounts'                   => 'nullable|array|max:9',
                'additional_accounts.*.principal_arrears' => 'nullable|numeric',
                'additional_accounts.*.interest_arrears'  => 'nullable|numeric',
                'additional_accounts.*.penalty_fee'       => 'nullable|numeric',
            ]);

            $additionalAccounts = $request->additional_accounts ?? [];
            $isKonven = strtolower($request->bank_name) === 'konvensional';
            
            $subjectTexts = [
                'SPKP' => $isKonven ? 'Pemberitahuan Kewajiban Kredit' : 'Pemberitahuan Kewajiban Pembiayaan',
                'SP1'  => 'Surat Peringatan I',
                'SP2'  => 'Surat Peringatan II',
                'SP3'  => 'Surat Peringatan III',
            ];
            $subjectText = $subjectTexts[$code] ?? 'Surat Pemberitahuan Kewajiban';

            $principalInput = (float) $request->principal_arrears;
            $subrogationInput = (float) ($request->subrogation ?? 0);
            $totalPrincipalWithSubrogation = $principalInput + $subrogationInput;

            $prevDueDate = $request->previous_due_date;
            $daysOverdue = $request->days_overdue;
            $prevLetterNo = $request->previous_letter_number;
            $prevLetterDate = $request->previous_letter_date;

            $letter = Letter::create([
                'letter_type_id' => $request->letter_type_id,
                'letter_number'  => $this->generateLetterNumber($request->letter_type_id),
                'letter_date'    => $request->letter_date ?? now()->toDateString(),
                'user_id'        => Auth::id(),
                'recipient'      => $request->customer_name,
                'subject'        => $subjectText,
                'content_data'   => [
                    'account_number'         => $request->account_number,
                    'additional_accounts'    => $additionalAccounts,
                    'due_date'               => $request->due_date,
                    'letter_date'            => $request->letter_date ?? now()->toDateString(),
                    
                    'previous_due_date'      => $prevDueDate,
                    'days_overdue'           => $daysOverdue,
                    'previous_letter_number' => $prevLetterNo,
                    'previous_letter_date'   => $prevLetterDate,
                    
                    'custom_data'            => [
                        'customer_name'          => $request->customer_name,
                        'principal_arrears'      => $totalPrincipalWithSubrogation, 
                        'subrogation'            => $subrogationInput,
                        'interest_arrears'       => $request->interest_arrears,
                        'penalty_fee'            => $request->penalty_fee,
                        'segment'                => $request->segment,
                        'bank_name'              => strtolower($request->bank_name),
                        'rm_name'                => $request->rm_name,
                        'rm_phone'               => $request->rm_phone,
                        
                        'previous_due_date'      => $prevDueDate,
                        'days_overdue'           => $daysOverdue,
                        'previous_letter_number' => $prevLetterNo,
                        'previous_letter_date'   => $prevLetterDate,
                    ]
                ],
            ]);

            return redirect()->route('letters.preview', $letter->id);
        }

        // ==========================================
        // 6. PINBUK-BB (Buka Blokir, Pemindahbukuan & Blokir Rekening Tabungan)
        // ==========================================
        elseif ($code === 'PINBUK-BB') {
            $request->validate([
                'letter_type_id'                  => 'required|exists:letter_types,id',
                'account_number'                  => 'required|string',
                'letter_date'                     => 'nullable|date',
                'custom_data.customer_name'       => 'nullable|string',
                'custom_data.loan_account'        => 'nullable|string',
                'custom_data.pinbuk_bb_sam_unit'  => 'required|in:SAM 1,SAM 2',
                'custom_data.table_buka_blokir'   => 'nullable|string',
                'custom_data.table_blokir_rekening' => 'nullable|string',
            ]);

            $customData = $request->input('custom_data', []);

            // PENGAMAN OTOMATIS: Jika customer_name dari form kosong, ambil otomatis dari BankAccount
            if (empty($customData['customer_name'])) {
                $bankAccount = BankAccount::where('account_number', $request->account_number)->first();
                $customData['customer_name'] = $bankAccount->customer_name ?? 'Nasabah';
            }

            if (empty($customData['loan_account'])) {
                $customData['loan_account'] = $request->account_number;
            }

            // Validasi rincian Buka Blokir: pokok, bunga/margin & denda boleh
            // kosong dengan nilai default 0; bila terisi harus berupa angka.
            if (!empty($customData['table_buka_blokir']) && is_string($customData['table_buka_blokir'])) {
                $tb1 = json_decode($customData['table_buka_blokir'], true);

                if (is_array($tb1) && isset($tb1['body']) && is_array($tb1['body'])) {
                    foreach ($tb1['body'] as $i => $row) {
                        if (!is_array($row)) {
                            continue;
                        }

                        foreach (['principal' => 'Pokok', 'interest' => 'Bunga/Margin', 'penalty' => 'Denda'] as $field => $label) {
                            $val = $row[$field] ?? null;

                            if ($val === null || $val === '') {
                                $row[$field] = 0;
                            } elseif (!is_numeric($val)) {
                                return back()->withInput()->with('error', 'Nilai ' . $label . ' pada tabel Buka Blokir harus berupa angka.');
                            } else {
                                $row[$field] = (float) $val;
                            }
                        }

                        $row['total'] = $row['principal'] + $row['interest'] + $row['penalty'];
                        $tb1['body'][$i] = $row;
                    }

                    $customData['table_buka_blokir'] = json_encode($tb1);
                }
            }

            // Validasi rincian Blokir Rekening: nominal blokir boleh kosong
            // dengan nilai default 0; bila terisi harus berupa angka.
            if (!empty($customData['table_blokir_rekening']) && is_string($customData['table_blokir_rekening'])) {
                $tb2 = json_decode($customData['table_blokir_rekening'], true);

                if (is_array($tb2) && isset($tb2['body']) && is_array($tb2['body'])) {
                    foreach ($tb2['body'] as $i => $row) {
                        if (!is_array($row)) {
                            continue;
                        }

                        $val = $row['block_amount'] ?? null;

                        if ($val === null || $val === '') {
                            $row['block_amount'] = 0;
                        } elseif (!is_numeric($val)) {
                            return back()->withInput()->with('error', 'Nilai Nominal Blokir pada tabel Blokir Rekening harus berupa angka.');
                        } else {
                            $row['block_amount'] = (float) $val;
                        }

                        $tb2['body'][$i] = $row;
                    }

                    $customData['table_blokir_rekening'] = json_encode($tb2);
                }
            }

            $letter = Letter::create([
                'letter_type_id' => $request->letter_type_id,
                'letter_number'  => $this->generateLetterNumber($request->letter_type_id),
                'letter_date'    => $request->letter_date ?? now()->toDateString(),
                'user_id'        => Auth::id(),
                'recipient'      => 'Strategy & Enabler Division Cq. Performance & Support Departement Head',
                'subject'        => 'Buka Blokir, Pemindahbukuan, dan Blokir Rekening Tabungan Setoran Debitur an. ' . $customData['customer_name'],
                'content_data'   => [
                    'account_number' => $request->account_number,
                    'custom_data'    => $customData,
                ],
            ]);

            return redirect()->route('letters.preview', $letter->id);
        }

        return back()->withInput()->with('error', 'Jenis surat dengan kode "' . $code . '" tidak dikenali sistem.');
    }

    /**
     * Preview HTML Surat
     */
    public function previewPdf($id)
    {
        $letter = Letter::with(['letterType', 'user'])->findOrFail($id);

        if (auth()->user()?->isTeamLeader() && $letter->user_id !== auth()->id()) {
            abort(403, 'Surat ini bukan milik Anda.');
        }
        
        $prepared = $this->letterService->prepareDataForTemplate($letter);
        $renderedContent = $this->letterService->renderTemplate($prepared['template'], $prepared['data']);

        return view('letters.pdf_template', [
            'letter'  => $letter,
            'content' => $renderedContent,
        ]);
    }

    /**
     * Export / Download PDF
     */
    public function exportPdf($id)
    {
        $letter = Letter::with(['letterType', 'user'])->findOrFail($id);

        if (auth()->user()?->isTeamLeader() && $letter->user_id !== auth()->id()) {
            abort(403, 'Surat ini bukan milik Anda.');
        }
        
        $prepared = $this->letterService->prepareDataForTemplate($letter);
        $renderedContent = $this->letterService->renderTemplate($prepared['template'], $prepared['data']);

        $pdf = Pdf::loadView('letters.pdf_template', [
            'letter'  => $letter,
            'content' => $renderedContent,
            'isPdf'   => true,
        ]);

        $pdf->setPaper('letter', 'portrait');
        $filename = 'Surat_Kewajiban_' . str_replace('/', '_', $letter->letter_number) . '.pdf';

        return $pdf->download($filename);
    }
}