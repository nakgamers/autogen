<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Buat Surat / Formulir') }}
        </h2>
    </x-slot>

   <!-- Include Select2 CSS -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
    .select2-container--default .select2-selection--single {
        height: 42px; border-color: #d1d5db; border-radius: 0.375rem; padding: 6px 12px;
    }
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 28px; color: #1f2937;
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 40px;
    }
</style>

<div class="py-12">
    <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">

            @if (session('error'))
                <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded-md">
                    {{ session('error') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded-md">
                    <ul class="list-disc list-inside text-sm">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('letters.store') }}" method="POST" id="letter_form">
                @csrf

                <!-- Jenis Surat -->
                <div class="mb-4">
                    <label for="letter_type_id" class="block text-sm font-medium text-gray-700 mb-1">Jenis Surat</label>
                    <select name="letter_type_id" id="letter_type_id" required class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                        @foreach ($letterTypes as $type)
                            <option value="{{ $type->id }}" data-code="{{ $type->code }}">{{ $type->code }} - {{ $type->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Pilih / Cari Rekening Nasabah (AJAX SEARCH) -->
                <div class="mb-4" id="main_account_search_wrapper">
                    <label for="account_number" class="block text-sm font-medium text-gray-700 mb-1" id="account_label">Cari / Pilih Rekening Utama Nasabah</label>
                    <select name="account_number" id="account_number" required class="w-full select2-ajax border-gray-300 rounded-md shadow-sm">
                        <!-- Option kosong, data akan diload via AJAX -->
                    </select>
                </div>

                <!-- Alert Notifikasi Rekening Terkait / Ganda (Khusus Penagihan) -->
                <div id="duplicate_name_alert" class="hidden mb-4 p-4 bg-yellow-50 border-l-4 border-yellow-400 text-yellow-800 rounded-r-md transition">
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-yellow-500 mt-0.5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3 w-full">
                            <p class="text-sm font-bold text-yellow-800" id="alert_title_text">Terdeteksi Rekening Lain Milik Nasabah Ini!</p>
                            <p class="text-xs text-yellow-700 mt-1">
                                Ditemukan <strong id="duplicate_count">0</strong> rekening lain. Total maksimal dalam daftar adalah <strong class="text-indigo-600">10 rekening</strong>. Klik tombol di bawah untuk menambahkan:
                            </p>
                            <div id="duplicate_accounts_container" class="mt-3 space-y-2"></div>
                        </div>
                    </div>
                </div>

                <!-- PANEL UNTUK PENAGIHAN (SPKP, SP1, SP2, SP3) -->
                <div id="collection_details_card" class="hidden mb-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                    <div class="flex justify-between items-center mb-3 border-b border-blue-200 pb-2">
                        <h4 class="text-xs font-bold text-blue-700 uppercase tracking-wider">Informasi Detail Rekening Penagihan</h4>
                    </div>
                    <p class="text-xs text-gray-600 mb-4 italic">Perubahan data di bawah ini hanya akan berlaku pada surat yang dicetak.</p>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-y-4 gap-x-4 text-sm mb-4">
                        <div>
                            <label class="block text-gray-700 font-medium mb-1">Nama Nasabah</label>
                            <input type="text" name="customer_name" id="edit_customer" class="w-full border-gray-300 rounded-md shadow-sm">
                        </div>
                        <div>
                            <label class="block text-gray-700 font-medium mb-1">Tunggakan Pokok</label>
                            <div class="flex">
                                <span class="bg-gray-200 border border-gray-300 border-r-0 rounded-l-md px-3 py-2 text-gray-600">Rp</span>
                                <input type="number" name="principal_arrears" id="edit_principal" step="any" class="w-full border-gray-300 rounded-r-md shadow-sm">
                            </div>
                        </div>
                        <div>
                            <label class="block text-gray-700 font-medium mb-1">Subrogasi</label>
                            <div class="flex">
                                <span class="bg-gray-200 border border-gray-300 border-r-0 rounded-l-md px-3 py-2 text-gray-600">Rp</span>
                                <input type="number" name="subrogation" id="edit_subrogation" value="0" step="any" min="0" class="w-full border-gray-300 rounded-r-md shadow-sm">
                            </div>
                        </div>
                        <div>
                            <label class="block text-gray-700 font-medium mb-1">Tunggakan Bunga/Margin</label>
                            <div class="flex">
                                <span class="bg-gray-200 border border-gray-300 border-r-0 rounded-l-md px-3 py-2 text-gray-600">Rp</span>
                                <input type="number" name="interest_arrears" id="edit_interest" step="any" class="w-full border-gray-300 rounded-r-md shadow-sm">
                            </div>
                        </div>
                        <div>
                            <label class="block text-gray-700 font-medium mb-1">Denda</label>
                            <div class="flex">
                                <span class="bg-gray-200 border border-gray-300 border-r-0 rounded-l-md px-3 py-2 text-gray-600">Rp</span>
                                <input type="number" name="penalty_fee" id="edit_penalty" step="any" class="w-full border-gray-300 rounded-r-md shadow-sm">
                            </div>
                        </div>
                        <div>
                            <label class="block text-gray-700 font-medium mb-1">Segmen</label>
                            <input type="text" name="segment" id="edit_segment" class="w-full bg-gray-100 text-gray-700 border-gray-300 rounded-md shadow-sm">
                        </div>
                        <div>
                            <label class="block text-gray-700 font-medium mb-1">Jenis Bank</label>
                            <select name="bank_name" id="edit_bank" class="w-full border-gray-300 rounded-md shadow-sm">
                                <option value="konvensional">Konvensional</option>
                                <option value="syariah">Syariah</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-gray-700 font-medium mb-1">RM Pengelola</label>
                            <input type="text" name="rm_name" id="edit_rm" class="w-full bg-gray-100 text-gray-700 border-gray-300 rounded-md shadow-sm">
                        </div>
                        <div>
                            <label class="block text-gray-700 font-medium mb-1">No. Telp RM</label>
                            <input type="text" name="rm_phone" id="edit_rm_phone" class="w-full bg-gray-100 text-gray-700 border-gray-300 rounded-md shadow-sm">
                        </div>
                    </div>

                    <!-- Tempat Detail Rekening Tambahan Penagihan -->
                    <div id="additional_details_container" class="space-y-3 mt-4 pt-4 border-t border-blue-200 empty:hidden"></div>
                </div>

                <!-- PANEL UNTUK FORMULIR PEMINJAMAN BUNDEL (FPBK) -->
                <div id="bundle_details_card" class="hidden mb-6 p-4 bg-emerald-50 border border-emerald-200 rounded-lg">
                    <div class="flex justify-between items-center mb-3 border-b border-emerald-200 pb-2">
                        <h4 class="text-xs font-bold text-emerald-700 uppercase tracking-wider">Informasi Detail Bundel Kredit / Pembiayaan Utama</h4>
                    </div>
                    <p class="text-xs text-gray-600 mb-4 italic">Data rincian bundel yang akan dimuat ke dalam tabel formulir peminjaman.</p>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-y-4 gap-x-4 text-sm mb-4">
                        <div>
                            <label class="block text-gray-700 font-medium mb-1">No Rekening</label>
                            <input type="text" name="bundle_main[account_number]" id="bundle_acc_no" readonly class="w-full bg-gray-100 border-gray-300 rounded-md shadow-sm">
                        </div>
                        <div>
                            <label class="block text-gray-700 font-medium mb-1">Nama Debitur</label>
                            <input type="text" name="bundle_main[customer_name]" id="bundle_cust_name" class="w-full border-gray-300 rounded-md shadow-sm">
                        </div>
                        <div>
                            <label class="block text-gray-700 font-medium mb-1">Segmen</label>
                            <input type="text" name="bundle_main[segment]" id="bundle_seg" class="w-full border-gray-300 rounded-md shadow-sm">
                        </div>
                        <div>
                            <label class="block text-gray-700 font-medium mb-1">Periode HB (Hapus Buku)</label>
                            <input type="text" name="bundle_main[hb_period]" id="bundle_hb" placeholder="Contoh: Desember 2025" class="w-full border-gray-300 rounded-md shadow-sm">
                        </div>
                        <div>
                            <label class="block text-gray-700 font-medium mb-1">Cabang</label>
                            <input type="text" name="bundle_main[branch]" id="bundle_branch" placeholder="Contoh: Kalimalang" class="w-full border-gray-300 rounded-md shadow-sm">
                        </div>
                        <div>
                            <label class="block text-gray-700 font-medium mb-1">Tujuan Peminjaman</label>
                            <input type="text" name="bundle_main[purpose]" id="bundle_purpose" value="Pelunasan" class="w-full border-gray-300 rounded-md shadow-sm">
                        </div>
                    </div>

                    <!-- TOMBOL TAMBAH DEBITUR / AKUN LAIN SECARA BEBAS (KHUSUS FPBK) -->
                    <div id="add_custom_debtor_wrapper" class="hidden mt-4 pt-3 border-t border-emerald-200">
                        <button type="button" id="btn_open_debtor_modal" class="px-3 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-md text-xs font-medium transition shadow-sm">
                            + Tambah Debitur / Rekening Lain ke Bundel (Maks 10)
                        </button>
                    </div>

                    <!-- Tempat Detail Bundel Tambahan -->
                    <div id="bundle_additional_container" class="space-y-3 mt-4 pt-4 border-t border-emerald-200 empty:hidden"></div>
                </div>

                <!-- PANEL PINBUK -->
                <div id="pinbuk_details_card" class="hidden mb-6 p-4 bg-teal-50 border border-teal-200 rounded-lg">
                    <h4 class="text-xs font-bold text-teal-700 uppercase tracking-wider mb-3">Informasi Detail Memo Pemindahbukuan (PINBUK)</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm mb-4">
                        <div>
                            <label class="block text-gray-700 font-medium mb-1">Pilih Unit SAM</label>
                            <select name="custom_data[pinbuk_sam_unit]" id="pinbuk_sam_unit" class="w-full border-gray-300 rounded-md shadow-sm">
                                <option value="SAM 1">SAM 1 (Department Head: Yenni Nainggolan)</option>
                                <option value="SAM 2">SAM 2 (Department Head: Muslim)</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-gray-700 font-medium mb-1">Nama Debitur</label>
                            <input type="text" name="custom_data[customer_name]" id="pinbuk_customer_name" class="w-full border-gray-300 rounded-md shadow-sm">
                        </div>
                        <div>
                            <label class="block text-gray-700 font-medium mb-1">Nama Penyetor</label>
                            <input type="text" name="custom_data[depositor_name]" id="pinbuk_depositor_name" class="w-full border-gray-300 rounded-md shadow-sm">
                        </div>
                        <div>
                            <label class="block text-gray-700 font-medium mb-1">No. Rekening Pendebetan</label>
                            <input type="text" name="custom_data[debit_account]" id="pinbuk_debit_account" value="91392217027" class="w-full border-gray-300 rounded-md shadow-sm">
                        </div>
                        <div>
                            <label class="block text-gray-700 font-medium mb-1">Nama Rekening Pendebetan</label>
                            <input type="text" name="custom_data[debit_account_name]" id="pinbuk_debit_account_name" value="Setoran Angsuran Krdt Konsumtip NKUK" class="w-full border-gray-300 rounded-md shadow-sm">
                        </div>
                        <div>
                            <label class="block text-gray-700 font-medium mb-1">No. Rekening Pinjaman</label>
                            <input type="text" name="custom_data[loan_account]" id="pinbuk_loan_account" class="w-full bg-gray-100 border-gray-300 rounded-md shadow-sm" readonly>
                        </div>
                        <div>
                            <label class="block text-gray-700 font-medium mb-1">Cabang / Capem</label>
                            <input type="text" name="custom_data[branch]" id="pinbuk_branch" class="w-full border-gray-300 rounded-md shadow-sm">
                        </div>
                        <div>
                            <label class="block text-gray-700 font-medium mb-1">Periode HB (Hapus Buku)</label>
                            <input type="text" name="custom_data[hb_period]" id="pinbuk_hb_period" placeholder="Contoh: Oktober 2025" class="w-full border-gray-300 rounded-md shadow-sm">
                        </div>
                        <div>
                            <label class="block text-gray-700 font-medium mb-1">Jumlah Setoran</label>
                            <div class="flex">
                                <span class="bg-gray-200 border border-gray-300 border-r-0 rounded-l-md px-3 py-2 text-gray-600">Rp</span>
                                <input type="number" name="custom_data[deposit_amount]" id="pinbuk_deposit_amount" step="any" class="w-full border-gray-300 rounded-r-md shadow-sm">
                            </div>
                        </div>
                        <div>
                            <label class="block text-gray-700 font-medium mb-1">Tanggal Setoran</label>
                            <input type="date" name="custom_data[deposit_date]" id="pinbuk_deposit_date" value="{{ date('Y-m-d') }}" class="w-full border-gray-300 rounded-md shadow-sm">
                        </div>
                        <div>
                            <label class="block text-gray-700 font-medium mb-1">Tujuan Pendebetan</label>
                            <input type="text" name="custom_data[debit_purpose]" id="pinbuk_debit_purpose" value="Setoran pokok kredit" class="w-full border-gray-300 rounded-md shadow-sm">
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-gray-700 font-medium mb-1">Catatan Update SLIK</label>
                            <input type="text" name="custom_data[slik_update_note]" id="pinbuk_slik_note" value="Mohon di update sesuai baki debet seharusnya" class="w-full border-gray-300 rounded-md shadow-sm">
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-gray-700 font-medium mb-1">Verifikasi Baki debet dari BV</label>
                            <textarea name="custom_data[pinbuk_description]" id="pinbuk_description" rows="3" placeholder="Masukkan keterangan tambahan jika diperlukan..." class="w-full border-gray-300 rounded-md shadow-sm"></textarea>
                        </div>
                    </div>
                </div>

                <!-- PANEL UNTUK MEMO BIAYA (MMO) -->
                <div id="mmo_details_card" class="hidden mb-6 p-4 bg-purple-50 border border-purple-200 rounded-lg">
                    <div class="flex justify-between items-center mb-3 border-b border-purple-200 pb-2">
                        <h4 class="text-xs font-bold text-purple-700 uppercase tracking-wider">Informasi Detail Memo Biaya (MMO)</h4>
                    </div>
                    <p class="text-xs text-gray-600 mb-4 italic">Pilih unit pengirim, tujuan divisi penerima memo, serta daftar debitur yang akan dikonfirmasi.</p>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="block text-gray-700 font-medium mb-1">Pilih Unit Pengirim (Dari)</label>
                            <select name="sam_unit" id="sam_unit" class="w-full border-gray-300 rounded-md shadow-sm text-sm">
                                <option value="SAM 1">SAM 1 (Department Head: Yenni Nainggolan)</option>
                                <option value="SAM 2">SAM 2 (Department Head: Muslim)</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-gray-700 font-medium mb-1">Tujuan / Penerima Memo</label>
                            <select name="memo_target" id="memo_target" class="w-full border-gray-300 rounded-md shadow-sm text-sm">
                                <option value="support">Strategy & Enabler Division (Performance & Support)</option>
                                <option value="auction">Write-Off Loan & Auction Division (Auction Dept)</option>
                            </select>
                        </div>
                    </div>

                    <div class="mt-4 pt-3 border-t border-purple-200">
                        <div class="flex justify-between items-center mb-2">
                            <span class="text-xs font-bold text-purple-800">Daftar Debitur (Maks 10)</span>
                            <button type="button" id="btn_add_mmo_debtor" class="px-3 py-1.5 bg-purple-600 hover:bg-purple-700 text-white rounded text-xs font-medium transition shadow-sm">
                                + Tambah Debitur (Cari Rekening)
                            </button>
                        </div>
                        <div id="mmo_debtors_container" class="space-y-3"></div>
                    </div>
                </div>

                <!-- PANEL UNTUK MEMO PENYERAHAN JAMINAN (PPJ) -->
                <div id="ppj_details_card" class="hidden mb-6 p-4 bg-amber-50 border border-amber-200 rounded-lg">
                    <div class="flex justify-between items-center mb-3 border-b border-amber-200 pb-2">
                        <h4 class="text-xs font-bold text-amber-700 uppercase tracking-wider">Informasi Detail Permintaan Penyerahan Jaminan (PPJ)</h4>
                    </div>
                    <p class="text-xs text-gray-600 mb-4 italic">Lengkapi data rincian agunan dan referensi SPPK untuk memorandum penyerahan jaminan.</p>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-y-4 gap-x-4 text-sm mb-4">
                        <div>
                            <label class="block text-gray-700 font-medium mb-1">Nama Debitur</label>
                            <input type="text" name="custom_data[customer_name]" id="ppj_customer_name" class="w-full border-gray-300 rounded-md shadow-sm">
                        </div>
                        <div>
                            <label class="block text-gray-700 font-medium mb-1">No Rekening</label>
                            <input type="text" name="custom_data[account_number]" id="ppj_account_number" readonly class="w-full bg-gray-100 border-gray-300 rounded-md shadow-sm">
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-gray-700 font-medium mb-1">Rincian Agunan</label>
                            <textarea name="custom_data[collateral_details]" id="ppj_collateral" rows="2" placeholder="Contoh: SHM No. 06952/Sunter Jaya an. Yusnani" class="w-full border-gray-300 rounded-md shadow-sm text-sm"></textarea>
                        </div>
                        <div>
                            <label class="block text-gray-700 font-medium mb-1">No. SPPK</label>
                            <input type="text" name="custom_data[sppk_number]" id="ppj_sppk_number" placeholder="Contoh: 6834/SPPK/SAM/X/2026" class="w-full border-gray-300 rounded-md shadow-sm">
                        </div>
                        <div>
                            <label class="block text-gray-700 font-medium mb-1">Tanggal SPPK</label>
                            <input type="date" name="custom_data[sppk_date]" id="ppj_sppk_date" value="{{ date('Y-m-d') }}" class="w-full border-gray-300 rounded-md shadow-sm">
                        </div>
                        <div>
                            <label class="block text-gray-700 font-medium mb-1">Paling Lambat Tanggal (Deadline)</label>
                            <input type="date" name="custom_data[deadline_date]" id="ppj_deadline_date" value="{{ date('Y-m-d', strtotime('+7 days')) }}" class="w-full border-gray-300 rounded-md shadow-sm">
                        </div>
                        <div>
                            <label class="block text-gray-700 font-medium mb-1">Pilih Unit SAM</label>
                            <select name="custom_data[ppj_sam_unit]" id="ppj_sam_unit" class="w-full border-gray-300 rounded-md shadow-sm">
                                <option value="SAM 1">SAM 1 (Department Head: Yenni Nainggolan)</option>
                                <option value="SAM 2">SAM 2 (Department Head: Muslim)</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Tanggal Surat & Batas Waktu -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4" id="date_fields_wrapper">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1" id="label_letter_date">Tanggal Surat</label>
                        <input type="date" name="letter_date" id="letter_date" value="{{ date('Y-m-d') }}" required class="w-full border-gray-300 rounded-md shadow-sm">
                    </div>
                    <div id="due_date_wrapper">
                        <label class="block text-sm font-medium text-gray-700 mb-1" id="label_due_date">Batas Tanggal Penyelesaian</label>
                        <input type="date" name="due_date" id="due_date" value="{{ date('Y-m-d', strtotime('+14 days')) }}" required class="w-full border-gray-300 rounded-md shadow-sm">
                    </div>
                </div>

                <!-- FIELD KHUSUS SP1, SP2, & SP3 -->
                <div id="sp1_extra_fields" class="hidden mb-4 p-4 bg-red-50 border border-red-200 rounded-lg">
                    <h4 class="text-sm font-bold text-red-700 mb-3" id="extra_fields_title">Informasi Tambahan Surat Peringatan</h4>
                    
                    <!-- Field untuk SP1 -->
                    <div id="fields_sp1" class="hidden grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Jatuh Tempo Sebelumnya</label>
                            <input type="date" name="previous_due_date" id="previous_due_date" class="w-full border-gray-300 rounded-md shadow-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Lama Menunggak (Otomatis)</label>
                            <div class="flex">
                                <input type="number" name="days_overdue" id="days_overdue" readonly placeholder="Otomatis terhitung..." class="w-full bg-gray-100 border-gray-300 rounded-l-md shadow-sm">
                                <span class="bg-gray-200 border border-gray-300 border-l-0 rounded-r-md px-3 py-2 text-gray-600">Hari</span>
                            </div>
                        </div>
                    </div>

                    <!-- Field untuk SP2 -->
                    <div id="fields_sp2" class="hidden grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">No. Surat SP 1 Sebelumnya</label>
                            <input type="text" name="previous_letter_number" id="sp2_prev_number" placeholder="Contoh: 001/SP1/WOLSAM/X/2026" class="w-full border-gray-300 rounded-md shadow-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Surat SP 1</label>
                            <input type="date" name="previous_letter_date" id="sp2_prev_date" class="w-full border-gray-300 rounded-md shadow-sm">
                        </div>
                    </div>

                    <!-- Field untuk SP3 -->
                    <div id="fields_sp3" class="hidden grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">No. Surat SP 2 Sebelumnya</label>
                            <input type="text" name="previous_letter_number" id="sp3_prev_number" placeholder="Contoh: 002/SP2/WOLSAM/X/2026" class="w-full border-gray-300 rounded-md shadow-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Surat SP 2</label>
                            <input type="date" name="previous_letter_date" id="sp3_prev_date" class="w-full border-gray-300 rounded-md shadow-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Deadline Pembayaran SP 2</label>
                            <input type="date" name="previous_due_date" id="sp3_prev_due" class="w-full border-gray-300 rounded-md shadow-sm">
                        </div>
                    </div>
                </div>

                <div class="flex justify-end gap-3 mt-6">
                    <a href="{{ route('letters.index') }}" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md text-sm">Batal</a>
                    <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 font-medium text-sm">Generate Surat / Formulir</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Include jQuery & Select2 JS -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
  $(document).ready(function() {
    let addedDebutorsCount = 1;

    // 1. SELECT2 AJAX UTAMA DENGAN PEMETAAN ID & TEXT YANG BENAR
    const $accountSelect =$('.select2-ajax').select2({
        placeholder: "-- Ketik No. Rekening atau Nama Nasabah --",
        allowClear: true,
        width: '100%',
        minimumInputLength: 3,
        ajax: {
            url: "{{ route('api.bank-accounts.search') }}",
            dataType: 'json',
            delay: 250,
            data: function (params) { return { search: params.term }; },
            processResults: function (data) {
                return {
                    results: data.map(function (item) {
                        return {
                            id: item.account_number,
                            text: item.account_number + ' - ' + item.customer_name,
                            account_number: item.account_number,
                            customer_name: item.customer_name,
                            principal_arrears: item.principal_arrears,
                            interest_arrears: item.interest_arrears,
                            penalty_fee: item.penalty_fee,
                            segment: item.segment,
                            bank_name: item.bank_name,
                            rm_name: item.rm_name,
                            rm_phone: item.rm_phone,
                            branch: item.branch,          
                            hb_period: item.hb_period,
                            collateral_details: item.collateral_details,
                            related_accounts: item.related_accounts
                        };
                    })
                };
            },
            cache: true
        }
    });

    // HANDLE SUBMIT FORM (BERSIHKAN BARIS MMO KOSONG)
    $('#letter_form').on('submit', function(e) {
        const selectedCode = $('#letter_type_id').find(':selected').data('code');
        if (selectedCode === 'MMO') {
            let validCount = 0;
            $('#mmo_debtors_container > div').each(function() {
                const accVal = $(this).find('input[name*="[account_number]"]').val();
                const custVal = $(this).find('input[name*="[customer_name]"]').val();
                if (!accVal || accVal.trim() === '' || !custVal || custVal.trim() === '') {
                    $(this).remove(); 
                } else {
                    validCount++;
                }
            });

            if (validCount === 0) {
                alert('Harap pilih dan isi minimal 1 debitur untuk Memo Biaya (MMO).');
                e.preventDefault();
                return false;
            }
        }
    });

    // Handle perubahan Jenis Surat
    $('#letter_type_id').on('change', function() {
        const selectedCode = $(this).find(':selected').data('code');$accountSelect.val(null).trigger('change');
        clearAllPanels();

        if (selectedCode === 'FPBK') {
            $('#main_account_search_wrapper').show();
            $('#account_number').prop('required', true);
            $('#account_label').text('Cari / Pilih Rekening Utama Bundel');
            $('#due_date_wrapper').hide();
            $('#date_fields_wrapper').show();
            $('#add_custom_debtor_wrapper').removeClass('hidden');
        } else if (selectedCode === 'PPJ') {
            $('#main_account_search_wrapper').show();
            $('#account_number').prop('required', true);
            $('#account_label').text('Cari / Pilih Rekening Debitur');
            $('#due_date_wrapper').hide();
            $('#date_fields_wrapper').show();
        } else if (selectedCode === 'PINBUK') {
            $('#main_account_search_wrapper').show();
            $('#account_number').prop('required', true);
            $('#account_label').text('Cari / Pilih Rekening Pinjaman Debitur');
            $('#due_date_wrapper').hide();
            $('#pinbuk_details_card').removeClass('hidden');  
        } else if (selectedCode === 'MMO') {
            $('#main_account_search_wrapper').hide();
            $('#account_number').prop('required', false);
            $('#due_date_wrapper').show();
            $('#date_fields_wrapper').show();
            $('#mmo_details_card').removeClass('hidden');
            
            if ($('#mmo_debtors_container').children().length === 0) {
                addMmoDebtorRow();
            }
        } else {
            $('#main_account_search_wrapper').show();
            $('#account_label').text('Cari / Pilih Rekening Utama Nasabah');
            $('#due_date_wrapper').show();
            $('#date_fields_wrapper').show();
            $('#add_custom_debtor_wrapper').addClass('hidden');
        }

        $('#sp1_extra_fields').addClass('hidden');
        $('#fields_sp1').addClass('hidden').find('input').prop('disabled', true);
        $('#fields_sp2').addClass('hidden').find('input').prop('disabled', true);
        $('#fields_sp3').addClass('hidden').find('input').prop('disabled', true);

        if (['SP1', 'SP2', 'SP3'].includes(selectedCode)) {
            $('#sp1_extra_fields').removeClass('hidden');

            if (selectedCode === 'SP1') {
                $('#extra_fields_title').text('Informasi Tambahan Surat Peringatan 1 (SP 1)');
                $('#fields_sp1').removeClass('hidden').find('input').prop('disabled', false);
            } else if (selectedCode === 'SP2') {
                $('#extra_fields_title').text('Informasi Tambahan Surat Peringatan 2 (SP 2)');
                $('#fields_sp2').removeClass('hidden').find('input').prop('disabled', false);
            } else if (selectedCode === 'SP3') {
                $('#extra_fields_title').text('Informasi Tambahan Surat Peringatan 3 (SP 3)');
                $('#fields_sp3').removeClass('hidden').find('input').prop('disabled', false);
            }
        }
    }).trigger('change');

    // Kalkulasi otomatis selisih hari untuk SP1
    $('#letter_date, #previous_due_date').on('change', function() {
        const letterDateVal = $('#letter_date').val();
        const prevDueDateVal = $('#previous_due_date').val();

        if (letterDateVal && prevDueDateVal) {
            const date1 = new Date(prevDueDateVal);
            const date2 = new Date(letterDateVal);
            const diffTime = date2 - date1;
            const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));

            $('#days_overdue').val(diffDays > 0 ? diffDays : 0);
        } else {
            $('#days_overdue').val('');
        }
    });

    function clearAllPanels() {
        $('#collection_details_card').addClass('hidden');
        $('#bundle_details_card').addClass('hidden');
        $('#ppj_details_card').addClass('hidden');
        $('#mmo_details_card').addClass('hidden');
        $('#pinbuk_details_card').addClass('hidden');
        $('#duplicate_name_alert').addClass('hidden');
        $('#additional_details_container').empty();
        $('#bundle_additional_container').empty();
        $('#mmo_debtors_container').empty();
        addedDebutorsCount = 1;
    }

    // KETIKA REKENING UTAMA DIPILIH
    $accountSelect.on('select2:select', function(e) {
        const data = e.params.data;
        const selectedCode = $('#letter_type_id').find(':selected').data('code');

        // Pastikan select utama memegang nilai account_number dengan benar
        if ($('#account_number').find("option[value='" + data.account_number + "']").length === 0) {
            var newOption = new Option(data.text, data.account_number, true, true);
            $('#account_number').append(newOption).trigger('change');
        } else {
            $('#account_number').val(data.account_number).trigger('change');
        }

        if (selectedCode === 'FPBK') {
            $('#bundle_acc_no').val(data.account_number);
            $('#bundle_cust_name').val(data.customer_name);
            $('#bundle_seg').val(data.segment || 'KPR');
            $('#bundle_hb').val(data.hb_period || '');
            $('#bundle_branch').val(data.branch || '');
            $('#bundle_purpose').val('Pelunasan');
            $('#bundle_details_card').removeClass('hidden');
        } else if (selectedCode === 'PPJ') {
            $('#ppj_account_number').val(data.account_number);
            $('#ppj_customer_name').val(data.customer_name);
            $('#ppj_collateral').val(data.collateral_details || '');
            $('#ppj_details_card').removeClass('hidden');
        } else if (selectedCode === 'PINBUK') {
            $('#pinbuk_loan_account').val(data.account_number);
            $('#pinbuk_customer_name').val(data.customer_name); 
            $('#pinbuk_depositor_name').val(data.customer_name);
            $('#pinbuk_branch').val(data.branch || '');
            $('#pinbuk_hb_period').val(data.hb_period || '');
        } else {
            $('#edit_customer').val(data.customer_name);
            $('#edit_principal').val(data.principal_arrears || 0); 
            $('#edit_subrogation').val(0);
            $('#edit_interest').val(data.interest_arrears || 0);
            $('#edit_penalty').val(data.penalty_fee || 0);
            $('#edit_segment').val(data.segment || '');
            $('#edit_bank').val(data.bank_name ? data.bank_name.toLowerCase() : 'konvensional');
            $('#edit_rm').val(data.rm_name);
            $('#edit_rm_phone').val(data.rm_phone);
            $('#collection_details_card').removeClass('hidden');

            if (data.related_accounts && data.related_accounts.length > 0) {
                $('#duplicate_count').text(data.related_accounts.length);
                let alertHtml = '';

                data.related_accounts.forEach((acc, idx) => {
                    if (idx < 9) {
                        alertHtml += `
                            <div class="flex items-center justify-between bg-white p-2 rounded border border-yellow-200 text-xs">
                                <span><strong>${acc.account_number}</strong> (${acc.segment || 'Kredit'}) - Pokok: Rp ${Number(acc.principal_arrears || 0).toLocaleString('id-ID')}</span>
                                <button type="button" class="px-2 py-1 bg-indigo-600 text-white rounded text-xs btn-add-related" data-acc='${JSON.stringify(acc)}'>[+ Tambahkan]</button>
                            </div>
                        `;
                    }
                });

                $('#duplicate_accounts_container').html(alertHtml);
                $('#duplicate_name_alert').removeClass('hidden');
            } else {
                $('#duplicate_name_alert').addClass('hidden');
            }
        }
    });

    function addMmoDebtorRow(data = null) {
        const uniqueId = 'mmo_' + Date.now() + Math.random().toString(36).substring(2, 5);
        const rowHtml = `
            <div id="row_${uniqueId}" class="p-3 bg-white border border-purple-300 rounded-md shadow-sm relative space-y-2">
                <div class="flex justify-between items-center border-b border-purple-100 pb-1">
                    <span class="text-xs font-bold text-purple-900">Debitur / Rekening</span>
                    <button type="button" class="text-red-600 hover:text-red-800 text-xs font-bold btn-remove-mmo-row" data-id="${uniqueId}">[Hapus]</button>
                </div>

                <div class="mb-2">
                    <label class="block text-gray-600 text-xs mb-1">Cari No. Rekening / Nama Debitur</label>
                    <select class="w-full select2-mmo-ajax border-gray-300 rounded-md shadow-sm text-xs" data-unique="${uniqueId}">
                        <option></option>
                    </select>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-5 gap-2 text-xs">
                    <div>
                        <label class="block text-gray-500 mb-1">No Rekening</label>
                        <input type="text" name="debtors[${uniqueId}][account_number]" id="mmo_acc_${uniqueId}" value="${data ? data.account_number : ''}" class="w-full text-xs border-gray-300 rounded" required>
                    </div>
                    <div>
                        <label class="block text-gray-500 mb-1">Nama Debitur</label>
                        <input type="text" name="debtors[${uniqueId}][customer_name]" id="mmo_cust_${uniqueId}" value="${data ? data.customer_name : ''}" class="w-full text-xs border-gray-300 rounded" required>
                    </div>
                    <div>
                        <label class="block text-gray-500 mb-1">Periode Hapus Buku</label>
                        <input type="text" name="debtors[${uniqueId}][hb_period]" id="mmo_hb_${uniqueId}" value="${data && data.hb_period ? data.hb_period : 'Oktober 2024'}" class="w-full text-xs border-gray-300 rounded">
                    </div>
                    <div>
                        <label class="block text-gray-500 mb-1">Segmen</label>
                        <input type="text" name="debtors[${uniqueId}][segment]" id="mmo_seg_${uniqueId}" value="${data && data.segment ? data.segment : 'KPR'}" class="w-full text-xs border-gray-300 rounded">
                    </div>
                    <div>
                        <label class="block text-gray-500 mb-1">Cabang</label>
                        <input type="text" name="debtors[${uniqueId}][branch]" id="mmo_branch_${uniqueId}" value="${data && data.branch ? data.branch : 'KCP Mampang Prapatan'}" class="w-full text-xs border-gray-300 rounded">
                    </div>
                </div>
            </div>
        `;
        $('#mmo_debtors_container').append(rowHtml);

        $(`select[data-unique="${uniqueId}"]`).select2({
            placeholder: "-- Ketik No. Rekening atau Nama Debitur --",
            width: '100%',
            minimumInputLength: 3,
            ajax: {
                url: "{{ route('api.bank-accounts.search') }}",
                dataType: 'json',
                delay: 250,
                data: function (params) { return { search: params.term }; },
                processResults: function (resultData) {
                    return {
                        results: resultData.map(function (item) {
                            return {
                                id: item.account_number,
                                text: item.account_number + ' - ' + item.customer_name,
                                account_number: item.account_number,
                                customer_name: item.customer_name,
                                hb_period: item.hb_period,
                                segment: item.segment,
                                branch: item.branch
                            };
                        })
                    };
                },
                cache: true
            }
        }).on('select2:select', function(e) {
            const selectedData = e.params.data;
            const uid = $(this).data('unique');

            $('#mmo_acc_' + uid).val(selectedData.account_number);
            $('#mmo_cust_' + uid).val(selectedData.customer_name);
            $('#mmo_hb_' + uid).val(selectedData.hb_period || 'Oktober 2024');
            $('#mmo_seg_' + uid).val(selectedData.segment || 'KPR');
            $('#mmo_branch_' + uid).val(selectedData.branch || '-');
        });
    }

    $('#btn_add_mmo_debtor').on('click', function() {
        if ($('#mmo_debtors_container').children().length >= 10) {
            alert('Maksimal 10 debitur dalam satu memo.');
            return;
        }
        addMmoDebtorRow();
    });

    $(document).on('click', '.btn-remove-mmo-row', function() {
        $('#row_' + $(this).data('id')).remove();
    });

    $(document).on('click', '.btn-add-related', function() {
        const acc = $(this).data('acc');
        const uniqueKey = acc.account_number.replace(/[^a-zA-Z0-9]/g, '_');

        if ($(`#wrapper_rel_${uniqueKey}`).length > 0) {
            alert('Rekening ini sudah ditambahkan.');
            return;
        }

        const detailHtml = `
            <div id="wrapper_rel_${uniqueKey}" class="p-3 bg-white border border-blue-200 rounded-md text-xs relative space-y-2">
                <div class="flex justify-between font-bold text-blue-800">
                    <span>Rekening Tambahan: ${acc.account_number}</span>
                    <button type="button" class="text-red-600 hover:text-red-800 btn-remove-rel" data-key="${uniqueKey}">[Hapus]</button>
                </div>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-2">
                    <div>
                        <label class="block text-gray-500">Pokok</label>
                        <input type="number" name="additional_accounts[${acc.account_number}][principal_arrears]" value="${acc.principal_arrears || 0}" class="w-full text-xs border-gray-300 rounded">
                    </div>
                    <div>
                        <label class="block text-gray-500">Bunga/Margin</label>
                        <input type="number" name="additional_accounts[${acc.account_number}][interest_arrears]" value="${acc.interest_arrears || 0}" class="w-full text-xs border-gray-300 rounded">
                    </div>
                    <div>
                        <label class="block text-gray-500">Denda</label>
                        <input type="number" name="additional_accounts[${acc.account_number}][penalty_fee]" value="${acc.penalty_fee || 0}" class="w-full text-xs border-gray-300 rounded">
                    </div>
                    <div>
                        <label class="block text-gray-500">Subrogasi</label>
                        <input type="number" name="additional_accounts[${acc.account_number}][subrogation]" value="0" class="w-full text-xs border-gray-300 rounded">
                    </div>
                </div>
                <input type="hidden" name="additional_accounts[${acc.account_number}][account_number]" value="${acc.account_number}">
            </div>
        `;

        $('#additional_details_container').append(detailHtml);
        $(this).prop('disabled', true).text('[Ditambahkan]').addClass('bg-gray-400').removeClass('bg-indigo-600');
    });

    $(document).on('click', '.btn-remove-rel', function() {
        const key = $(this).data('key');$('#wrapper_rel_' + key).remove();
    });

    $('#btn_open_debtor_modal').on('click', function() {
        if (addedDebutorsCount >= 10) {
            alert('Batas maksimal adalah 10 rekening / debitur dalam satu formulir.');
            return;
        }

        const uniqueId = 'extra_' + Date.now();

        const rowHtml = `
            <div id="row_debtor_${uniqueId}" class="p-3 bg-white border border-emerald-300 rounded-md shadow-sm relative space-y-3">
                <div class="flex justify-between items-center border-b border-emerald-100 pb-1">
                    <span class="text-xs font-bold text-emerald-800">Debitur Tambahan #${addedDebutorsCount}</span>
                    <button type="button" class="text-red-600 hover:text-red-800 text-xs font-bold btn-remove-debtor" data-id="${uniqueId}">[Hapus]</button>
                </div>
                
                <div>
                    <label class="block text-gray-600 text-xs mb-1">Cari Rekening Lain</label>
                    <select class="w-full select2-extra border-gray-300 rounded-md shadow-sm" data-unique="${uniqueId}">
                        <option></option>
                    </select>
                </div>

                <div id="fields_container_${uniqueId}" class="grid grid-cols-1 md:grid-cols-4 gap-3 text-xs hidden">
                    <div>
                        <label class="block text-gray-500 mb-1">No Rekening</label>
                        <input type="text" name="additional_bundles[${uniqueId}][account_number]" id="acc_${uniqueId}" readonly class="w-full text-xs bg-gray-100 border-gray-300 rounded-md">
                    </div>
                    <div>
                        <label class="block text-gray-500 mb-1">Nama Debitur</label>
                        <input type="text" name="additional_bundles[${uniqueId}][customer_name]" id="cust_${uniqueId}" class="w-full text-xs border-gray-300 rounded-md">
                    </div>
                    <div>
                        <label class="block text-gray-500 mb-1">Segmen</label>
                        <input type="text" name="additional_bundles[${uniqueId}][segment]" id="seg_${uniqueId}" class="w-full text-xs border-gray-300 rounded-md">
                    </div>
                    <div>
                        <label class="block text-gray-500 mb-1">Periode HB</label>
                        <input type="text" name="additional_bundles[${uniqueId}][hb_period]" id="hb_${uniqueId}" class="w-full text-xs border-gray-300 rounded-md">
                    </div>
                    <div>
                        <label class="block text-gray-500 mb-1">Cabang</label>
                        <input type="text" name="additional_bundles[${uniqueId}][branch]" id="branch_${uniqueId}" class="w-full text-xs border-gray-300 rounded-md">
                    </div>
                    <div class="md:col-span-3">
                        <label class="block text-gray-500 mb-1">Tujuan Peminjaman</label>
                        <input type="text" name="additional_bundles[${uniqueId}][purpose]" value="Pelunasan" class="w-full text-xs border-gray-300 rounded-md">
                    </div>
                </div>
            </div>
        `;

        $('#bundle_additional_container').append(rowHtml);
        addedDebutorsCount++;

        $(`select[data-unique="${uniqueId}"]`).select2({
            placeholder: "-- Cari No. Rekening / Nama Debitur Lain --",
            width: '100%',
            minimumInputLength: 3,
            ajax: {
                url: "{{ route('api.bank-accounts.search') }}",
                dataType: 'json',
                delay: 250,
                data: function (params) { return { search: params.term }; },
                processResults: function (data) {
                    return {
                        results: data.map(function (item) {
                            return {
                                id: item.account_number,
                                text: item.account_number + ' - ' + item.customer_name,
                                account_number: item.account_number,
                                customer_name: item.customer_name,
                                segment: item.segment,
                                hb_period: item.hb_period,
                                branch: item.branch
                            };
                        })
                    };
                },
                cache: true
            }
        }).on('select2:select', function(e) {
            const selectedData = e.params.data;
            const uid = $(this).data('unique');

            $('#acc_' + uid).val(selectedData.account_number);
            $('#cust_' + uid).val(selectedData.customer_name);
            $('#seg_' + uid).val(selectedData.segment || 'KPR');
            $('#hb_' + uid).val(selectedData.hb_period || '');
            $('#branch_' + uid).val(selectedData.branch || '');
            $('#fields_container_' + uid).removeClass('hidden');
        });
    });

    $(document).on('click', '.btn-remove-debtor', function() {
        const uid = $(this).data('id');$('#row_debtor_' + uid).remove();
        addedDebutorsCount--;
    });
  });
</script>

</x-app-layout>