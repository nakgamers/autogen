<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LetterController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\BankAccountController;
use App\Http\Controllers\UserManagementController;
use App\Http\Controllers\RmManagerController;

/*
|--------------------------------------------------------------------------
| Peta akses per role (31 Okt 2026)
| - super_admin      : upload rekening, kelola pengguna, kelola RM.
|                      Dashboard hanya sapaan. TIDAK bisa generate surat.
| - department_head  : dashboard pengawasan + riwayat semua Team Leader.
| - team_leader      : generate surat + dashboard & riwayat milik sendiri.
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return view('welcome');
});

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';

// Team Leader — generate surat
Route::middleware(['auth', 'role:team_leader'])->group(function () {
    Route::get('/letters/create', [LetterController::class, 'create'])->name('letters.create');
    Route::post('/letters', [LetterController::class, 'store'])->name('letters.store');
});

// Team Leader (milik sendiri) & Department Head (semua) — riwayat + preview/PDF
Route::middleware(['auth', 'role:team_leader,department_head'])->group(function () {
    Route::get('/letters', [LetterController::class, 'index'])->name('letters.index');
    Route::get('/letters/{id}/preview', [LetterController::class, 'previewPdf'])->name('letters.preview');
    Route::get('/letters/{id}/export-pdf', [LetterController::class, 'exportPdf'])->name('letters.export-pdf');
});

// Super Admin — upload & impor data rekening
Route::middleware(['auth', 'role:super_admin'])->group(function () {
    Route::get('/bank-accounts/upload', [BankAccountController::class, 'showUploadForm'])->name('bank-accounts.upload');
    Route::post('/bank-accounts/import', [BankAccountController::class, 'import'])->name('bank-accounts.import');
    Route::get('/bank-accounts/import', [BankAccountController::class, 'showUploadForm'])->name('bank-accounts.import');
    Route::post('/bank-accounts/import-ajax', [BankAccountController::class, 'importAjax'])->name('bank-accounts.import-ajax');
    Route::get('/api/import-status/{batchId}', [BankAccountController::class, 'importStatus']);
    Route::post('/bank-accounts/fix-row', [BankAccountController::class, 'fixAndSaveRow'])->name('bank-accounts.fix-row');

    // Super Admin — kelola pengguna (akun Team Leader & Department Head)
    Route::get('/users', [UserManagementController::class, 'index'])->name('users.index');
    Route::get('/users/create', [UserManagementController::class, 'create'])->name('users.create');
    Route::post('/users', [UserManagementController::class, 'store'])->name('users.store');
    Route::get('/users/{user}/edit', [UserManagementController::class, 'edit'])->name('users.edit');
    Route::patch('/users/{user}', [UserManagementController::class, 'update'])->name('users.update');
    Route::post('/users/{user}/reset-password', [UserManagementController::class, 'resetPassword'])->name('users.reset-password');
    Route::delete('/users/{user}', [UserManagementController::class, 'destroy'])->name('users.destroy');

    // Super Admin — kelola RM Manager (nama & nomor telepon)
    Route::get('/rm-managers', [RmManagerController::class, 'index'])->name('rm-managers.index');
    Route::get('/rm-managers/{rmManager}/edit', [RmManagerController::class, 'edit'])->name('rm-managers.edit');
    Route::patch('/rm-managers/{rmManager}', [RmManagerController::class, 'update'])->name('rm-managers.update');
});

// API pencarian rekening — dipakai form surat (Team Leader) & proses impor (Super Admin); wajib login
Route::middleware(['auth', 'role:team_leader,super_admin'])->group(function () {
    Route::get('/api/bank-accounts/search', [BankAccountController::class, 'search'])->name('api.bank-accounts.search');
    Route::get('/api/bank-accounts/check-duplicate', [BankAccountController::class, 'checkDuplicate'])->name('api.bank-accounts.check-duplicate');
});
