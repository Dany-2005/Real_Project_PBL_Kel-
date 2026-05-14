<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{
    ProfileController, ProdukController, KategoriController, 
    PelangganController, TransaksiController, SuplierController, 
    PembelianController, LaporanController, DashboardController, 
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
    Route::resource('transaksi', TransaksiController::class);
});

// --- 4. ROUTE KHUSUS PEMILIK ---
Route::middleware(['auth', 'role:pemilik'])->group(function () {
    Route::resource('pelanggan', PelangganController::class);
    Route::resource('produk', ProdukController::class);
    Route::resource('kategori', KategoriController::class); 
    Route::resource('suplier', SuplierController::class);
    Route::resource('pembelian', PembelianController::class);
    Route::resource('diskon', DiskonController::class);

    // --- PENGATURAN LANDING PAGE ---
   Route::prefix('pengaturan')->group(function () {
    Route::get('/landing', [LandingPageController::class, 'index'])->name('landing.index');
    Route::post('/landing/update', [LandingPageController::class, 'update'])->name('landing.update');
    
    // Gunakan LandingPageController saja biar satu pintu
    Route::post('/landing/slide', [LandingPageController::class, 'storeSlide'])->name('landing.slide.store');
    Route::delete('/landing/slide/{id}', [LandingPageController::class, 'destroySlide'])->name('landing.slide.destroy');
});

    Route::get('/laporan/export', [LaporanController::class, 'exportExcel'])->name('laporan.export');
    Route::post('/produk/{id}/transfer-stok', [ProdukController::class, 'transferStok'])->name('produk.transferStok');

    // --- GROUP PENGATURAN AKUN & USER ---
    Route::prefix('pengaturan')->name('pengaturan.')->group(function () {
        Route::get('/pemilik', [PengaturanController::class, 'pemilikIndex'])->name('pemilik');
        Route::put('/pemilik/update', [PengaturanController::class, 'pemilikUpdate'])->name('pemilik.update');

        Route::get('/kasir', [PengaturanController::class, 'kasir'])->name('kasir');
        Route::post('/kasir', [PengaturanController::class, 'kasirStore'])->name('kasir.store');
        Route::get('/kasir/{id}/edit', [PengaturanController::class, 'kasirEdit'])->name('kasir.edit');
        Route::put('/kasir/{id}', [PengaturanController::class, 'kasirUpdate'])->name('kasir.update');
        Route::delete('/kasir/{id}', [PengaturanController::class, 'kasirDestroy'])->name('kasir.destroy');

        Route::get('/suplier-user', [PengaturanController::class, 'suplier'])->name('suplier'); // Ganti dikit biar gak bentrok sama resource suplier
        Route::post('/suplier-user', [PengaturanController::class, 'suplierStore'])->name('suplier.store');
        Route::get('/suplier-user/{id}/edit', [PengaturanController::class, 'suplierEdit'])->name('suplier.edit');
        Route::put('/suplier-user/{id}', [PengaturanController::class, 'suplierUpdate'])->name('suplier.update');
        Route::delete('/suplier-user/{id}', [PengaturanController::class, 'suplierDestroy'])->name('suplier.destroy');
    });
});

require __DIR__.'/auth.php';