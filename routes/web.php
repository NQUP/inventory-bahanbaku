<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RedirectController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\ManagerController;
use App\Http\Controllers\GudangController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\PemesananController;
use App\Http\Controllers\BOMController;
use App\Http\Controllers\PermintaanController;
use App\Http\Controllers\EoqRopController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\BahanBakuController;
use App\Http\Controllers\ProfileController;

// ================= LOGIN ================
Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('dashboard')
        : redirect()->route('login');
});
Route::get('/dashboard', [RedirectController::class, 'redirectToDashboard'])->middleware('auth')->name('dashboard');
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// ================= ADMIN =================
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
    Route::resource('bom', BOMController::class);
    Route::get('/pemesanan', [PemesananController::class, 'index'])->name('pemesanan.index');
    Route::post('/pemesanan/{id}/setujui', [AdminController::class, 'setujui'])->name('pemesanan.setujui');
    Route::post('/pemesanan/{id}/tolak', [AdminController::class, 'tolak'])->name('pemesanan.tolak');
    Route::post('/permintaan/{id}/setujui', [PermintaanController::class, 'setujui'])->name('permintaan.setujui');
    Route::post('/permintaan/{id}/tolak', [PermintaanController::class, 'tolak'])->name('permintaan.tolak');
    Route::get('/eoq-rop/create/{id}', [EoqRopController::class, 'create'])->name('eoq-rop.create');
    Route::post('/eoq-rop/store', [EoqRopController::class, 'store'])->name('eoq-rop.store');
    Route::get('/pemesanan/export', [AdminController::class, 'exportPdf'])->name('pemesanan.export');
    Route::get('/pemesanan/export-excel', [AdminController::class, 'exportExcel'])->name('pemesanan.exportExcel');
});

// ================= MANAGER =================
Route::middleware(['auth', 'role:manager'])->prefix('manager')->name('manager.')->group(function () {
    Route::get('/dashboard', [ManagerController::class, 'dashboard'])->name('dashboard');
    Route::get('/permintaan', [ManagerController::class, 'permintaanIndex'])->name('permintaan.index');
    Route::post('/permintaan/{id}/setujui', [ManagerController::class, 'setujui'])->name('permintaan.setujui');
    Route::post('/permintaan/{id}/tolak', [ManagerController::class, 'tolak'])->name('permintaan.tolak');
    Route::get('/eoq-rop/create/{id}', [EoqRopController::class, 'create'])->name('eoq-rop.create');
    Route::post('/eoq-rop/store', [EoqRopController::class, 'store'])->name('eoq-rop.store');
    Route::get('/permintaan/riwayat', [ManagerController::class, 'riwayat'])->name('permintaan.riwayat');
    Route::get('/export/pdf', [ExportController::class, 'exportManagerPDF'])->name('export.manager.pdf');
    Route::get('/export/excel', [ExportController::class, 'exportManagerExcel'])->name('export.manager.excel');
});

// ================= GUDANG =================
Route::middleware(['auth', 'role:gudang'])->prefix('gudang')->name('gudang.')->group(function () {
    Route::get('/dashboard', [GudangController::class, 'dashboard'])->name('dashboard');
    Route::get('/permintaan', [GudangController::class, 'permintaanIndex'])->name('permintaan.index');
    Route::post('/permintaan/{id}/siapkan', [GudangController::class, 'siapkan'])->name('permintaan.siapkan');
    Route::post('/permintaan/{id}/kirim', [GudangController::class, 'kirim'])->name('permintaan.kirim');
    Route::put('/permintaan/{id}/terima', [GudangController::class, 'terima'])->name('permintaan.terima');
    Route::get('/riwayat-permintaan', [GudangController::class, 'riwayatPermintaan'])->name('permintaan.riwayat');
    Route::get('/riwayat-permintaan/export-pdf', [GudangController::class, 'exportRiwayatPdf'])->name('riwayat.export.pdf');
    Route::get('/riwayat-permintaan/export-excel', [GudangController::class, 'exportRiwayatExcel'])->name('riwayat.export.excel');
    Route::get('/permintaan/export-excel', [GudangController::class, 'exportExcel'])->name('permintaan.exportExcel');
});

Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::resource('bahanbaku', BahanBakuController::class)->except(['show']);
});

// ================= SUPPLIER =================
Route::middleware(['auth', 'role:supplier'])->prefix('supplier')->name('supplier.')->group(function () {
    Route::get('/dashboard', [SupplierController::class, 'dashboard'])->name('dashboard');
    Route::put('/pemesanan/{id}/kirim', [SupplierController::class, 'kirim'])->name('kirim');
    Route::put('/pemesanan/{id}/selesai', [SupplierController::class, 'selesai'])->name('selesai');
    Route::delete('/pemesanan/{id}/hapus', [SupplierController::class, 'hapus'])->name('hapus');
    Route::get('/pemesanan/{id}', [SupplierController::class, 'show'])->name('detail');
    Route::get('/riwayat', [SupplierController::class, 'riwayat'])->name('riwayat');
    Route::get('/riwayat/export/pdf', [SupplierController::class, 'exportRiwayatPdf'])->name('riwayat.pdf');
    Route::get('/riwayat/export/excel', [SupplierController::class, 'exportRiwayatExcel'])->name('riwayat.excel');
});

// ================= PEMESAN =================
Route::middleware(['auth', 'role:pemesan'])->prefix('pemesanan')->name('pemesanan.')->group(function () {
    Route::get('/dashboard', [PemesananController::class, 'dashboard'])->name('dashboard');
    Route::get('/create', [PemesananController::class, 'create'])->name('create');
    Route::post('/store', [PemesananController::class, 'store'])->name('store');
    Route::post('/konversi-preview', [PemesananController::class, 'konversiPreview'])->name('konversi-preview');
    Route::get('/{id}/edit', [PemesananController::class, 'edit'])->name('edit');
    Route::put('/{id}', [PemesananController::class, 'update'])->name('update');
    Route::delete('/{id}', [PemesananController::class, 'destroy'])->name('destroy');
    Route::get('/export/pdf', [PemesananController::class, 'exportPDF'])->name('export.pdf');
    Route::get('/export/excel', [PemesananController::class, 'exportExcel'])->name('export.excel');
    Route::post('/generate-kode', [PemesananController::class, 'generateKode'])->name('generateKode');
});

require __DIR__ . '/auth.php';
