<?php

namespace App\Http\Controllers;

use App\Models\Transaksi;
use App\Models\DetailTransaksi;
use App\Models\Produk;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request; 

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $today = Carbon::today();

        // Filter bulan, default bulan ini
        $bulan = $request->bulan ?? Carbon::now()->month;
        $tahun = $request->tahun ?? Carbon::now()->year;

        $selectedDate = Carbon::createFromDate($tahun, $bulan, 1);

        // 4 kartu statistik (tetap hari ini)
        $totalTransaksiHariIni = Transaksi::whereDate('tanggal', $today)->count();
        $totalPenjualanHariIni = Transaksi::whereDate('tanggal', $today)->sum('total');
        $produkTerjualHariIni  = DetailTransaksi::whereHas('transaksi', function($q) use ($today) {
            $q->whereDate('tanggal', $today);
        })->sum('jumlah');

        // Hitung stok menipis
        $stokMenipis = Produk::where('stok_toko', '<=', 10)
                                 ->orWhere('stok_gudang', '<=', 5)
                                 ->count();
        
        // Kita buat kedua variabel ini agar view tidak error
        $jumlahStokMenipis = $stokMenipis;

        // Transaksi terakhir
        $transaksiTerakhir = Transaksi::with(['detail.produk', 'pelanggan'])
            ->whereDate('tanggal', $today)
            ->latest('id_transaksi')
            ->take(5)
            ->get();

        // Produk terlaris
        $produkTerlaris = DetailTransaksi::whereHas('transaksi', function($q) use ($today) {
                $q->whereDate('tanggal', $today);
            })
            ->join('produk', 'detail_transaksi.id_produk', '=', 'produk.id_produk')
            ->select('produk.nama_produk', DB::raw('SUM(detail_transaksi.jumlah) as total_terjual'))
            ->groupBy('produk.id_produk', 'produk.nama_produk')
            ->orderByDesc('total_terjual')
            ->take(5)
            ->get();

        // Grafik per hari sesuai bulan yang dipilih
        $grafik = collect(range(0, $selectedDate->daysInMonth - 1))->map(function($i) use ($selectedDate) {
            $date = $selectedDate->copy()->addDays($i);
            return [
                'tanggal' => $date->format('d'),
                'total'   => Transaksi::whereDate('tanggal', $date)->sum('total'),
            ];
        });

        // Opsi bulan untuk dropdown
        $opsibulan = collect(range(1, Carbon::now()->month))->map(function($m) {
            $date = Carbon::createFromDate(Carbon::now()->year, $m, 1);
            return [
                'bulan' => $date->month,
                'tahun' => $date->year,
                'label' => $date->translatedFormat('F Y'),
            ];
        })->reverse()->values();

        return view('dashboard', compact(
            'totalTransaksiHariIni',
            'totalPenjualanHariIni',
            'produkTerjualHariIni',
            'stokMenipis',
            'jumlahStokMenipis',
            'transaksiTerakhir',
            'produkTerlaris',
            'grafik',
            'opsibulan',
            'bulan',
            'tahun'
        ));
    }
}