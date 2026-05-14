<?php

namespace App\Http\Controllers;

use App\Models\Transaksi;
use App\Models\Pembelian;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\LaporanPembelian;

class LaporanController extends Controller
{
    public function index(Request $request)
    {
        $dari  = $request->dari  ?? Carbon::now()->startOfMonth()->format('Y-m-d');
        $sampai = $request->sampai ?? Carbon::now()->endOfMonth()->format('Y-m-d');

        // Data yang boleh dilihat semua role (Pemilik & Kasir)
        $totalPenjualan = Transaksi::whereBetween('tanggal', [$dari, $sampai])->sum('total');
        $totalTransaksi = Transaksi::whereBetween('tanggal', [$dari, $sampai])->count();

        // Inisialisasi default 0
        $totalPembelian = 0;
        $labaBersih     = 0;

        // PROTEKSI LOGIKA: Hanya hitung jika role-nya Pemilik
        if (auth()->user()->role == 'pemilik') {
            $totalPembelian = LaporanPembelian::whereBetween('tanggal', [$dari, $sampai])->sum('total');
            $labaBersih     = $totalPenjualan - $totalPembelian;
        }

        // Data penjualan (tabel) - Tetap muncul untuk semua
        $transaksi = Transaksi::with(['pelanggan', 'detail.produk'])
            ->whereBetween('tanggal', [$dari, $sampai])
            ->latest('id_transaksi')
            ->paginate(10);

        // Produk terlaris (Bisa tetap ditampilkan atau diproteksi juga)
        $produkTerlaris = DB::table('detail_transaksi')
            ->join('transaksi', 'detail_transaksi.id_transaksi', '=', 'transaksi.id_transaksi')
            ->join('produk', 'detail_transaksi.id_produk', '=', 'produk.id_produk')
            ->whereBetween('transaksi.tanggal', [$dari, $sampai])
            ->select('produk.nama_produk', DB::raw('SUM(detail_transaksi.jumlah) as total_terjual'), DB::raw('SUM(detail_transaksi.subtotal) as total_pendapatan'))
            ->groupBy('produk.id_produk', 'produk.nama_produk')
            ->orderByDesc('total_terjual')
            ->take(5)
            ->get();

        return view('laporan.index', compact(
            'totalPenjualan',
            'totalTransaksi',
            'totalPembelian',
            'labaBersih',
            'transaksi',
            'produkTerlaris',
            'dari',
            'sampai'
        ));
    }

    public function exportExcel(Request $request)
    {
        // PROTEKSI: Jika kasir mencoba akses URL export langsung, tendang balik
        if (auth()->user()->role !== 'pemilik') {
            abort(403, 'Anda tidak memiliki akses untuk mengekspor data.');
        }

        $dari   = $request->dari   ?? Carbon::now()->startOfMonth()->format('Y-m-d');
        $sampai = $request->sampai ?? Carbon::now()->endOfMonth()->format('Y-m-d');

        $transaksi = Transaksi::with(['pelanggan', 'detail.produk'])
            ->whereBetween('tanggal', [$dari, $sampai])
            ->latest('id_transaksi')
            ->get();

        $filename = 'laporan_penjualan_' . $dari . '_sd_' . $sampai . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function() use ($transaksi) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['No', 'Tanggal', 'Pelanggan', 'Produk', 'Total', 'Metode Pembayaran']);
            foreach ($transaksi as $i => $t) {
                fputcsv($file, [
                    $i + 1,
                    Carbon::parse($t->tanggal)->format('d/m/Y'),
                    $t->pelanggan->nama_pelanggan ?? '-',
                    $t->detail->first()->produk->nama_produk ?? '-',
                    $t->total,
                    $t->metode_pembayaran ?? '-',
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}