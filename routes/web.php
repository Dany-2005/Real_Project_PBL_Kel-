<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{
    ProfileController, ProdukController, KategoriController,
    PelangganController, TransaksiController, SuplierController,
    LaporanController, DashboardController,
    PengaturanController, DiskonController, LandingPageController,
    LandingSlideController
};

Route::get('/', function () { return view('welcome'); });

// --- 1. ROUTE LOGIN UMUM ---
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// --- 2. ROUTE AKSES BERSAMA (Pemilik & Kasir) ---
Route::middleware(['auth', 'role:pemilik,kasir'])->group(function () {
    Route::get('/laporan', [LaporanController::class, 'index'])->name('laporan.index');
});

// --- 3. ROUTE KHUSUS KASIR ---
Route::middleware(['auth', 'role:kasir'])->group(function () {
    Route::resource('transaksi', TransaksiController::class)->only(['index', 'create', 'store', 'show', 'destroy']);
});

// --- 4. ROUTE KHUSUS PEMILIK ---
Route::middleware(['auth', 'role:pemilik'])->group(function () {
    Route::resource('pelanggan', PelangganController::class);
    Route::resource('produk', ProdukController::class);
    Route::resource('kategori', KategoriController::class);
    // FIX: suplier sekarang hanya pakai SuplierController (hapus duplikat di pengaturan)
    Route::resource('suplier', SuplierController::class);
    Route::resource('diskon', DiskonController::class);

    // Pembelian (lewat TransaksiController)
    Route::get('/pembelian', [TransaksiController::class, 'indexPembelian'])->name('pembelian.index');
    Route::get('/pembelian/create', [TransaksiController::class, 'createPembelian'])->name('pembelian.create');
    Route::post('/pembelian', [TransaksiController::class, 'storePembelian'])->name('pembelian.store');
    Route::get('/pembelian/{id}', [TransaksiController::class, 'showPembelian'])->name('pembelian.show');
    Route::delete('/pembelian/{id}', [TransaksiController::class, 'destroyPembelian'])->name('pembelian.destroy');

    // Landing Page
    Route::prefix('pengaturan')->group(function () {
        Route::get('/landing', [LandingPageController::class, 'index'])->name('landing.index');
        Route::post('/landing/update', [LandingPageController::class, 'update'])->name('landing.update');
        Route::post('/landing/slide', [LandingPageController::class, 'storeSlide'])->name('landing.slide.store');
        Route::delete('/landing/slide/{id}', [LandingPageController::class, 'destroySlide'])->name('landing.slide.destroy');
    });

    Route::get('/laporan/export', [LaporanController::class, 'exportExcel'])->name('laporan.export');
    Route::post('/produk/{id}/transfer-stok', [ProdukController::class, 'transferStok'])->name('produk.transferStok');

    // Pengaturan Akun & User
    Route::prefix('pengaturan')->name('pengaturan.')->group(function () {
        Route::get('/pemilik', [PengaturanController::class, 'pemilikIndex'])->name('pemilik');
        Route::put('/pemilik/update', [PengaturanController::class, 'pemilikUpdate'])->name('pemilik.update');

        Route::get('/kasir', [PengaturanController::class, 'kasir'])->name('kasir');
        Route::post('/kasir', [PengaturanController::class, 'kasirStore'])->name('kasir.store');
        Route::get('/kasir/{id}/edit', [PengaturanController::class, 'kasirEdit'])->name('kasir.edit');
        Route::put('/kasir/{id}', [PengaturanController::class, 'kasirUpdate'])->name('kasir.update');
        Route::delete('/kasir/{id}', [PengaturanController::class, 'kasirDestroy'])->name('kasir.destroy');

        Route::get('/suplier', function () {
            return redirect()->route('suplier.index');
        })->name('suplier');
    });
});

require __DIR__.'/auth.php';