<?php

namespace App\Http\Controllers;

use App\Models\Transaksi;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\LaporanPembelian;

class LaporanController extends Controller
{
    public function index(Request $request)
    {
        $dari   = $request->dari   ?? Carbon::now()->startOfMonth()->format('Y-m-d');
        $sampai = $request->sampai ?? Carbon::now()->endOfMonth()->format('Y-m-d');
        $tab    = $request->tab    ?? 'penjualan'; // default tab penjualan

        // Statistik (semua role bisa lihat)
        $totalPenjualan = Transaksi::where('jenis', 'penjualan')
            ->whereBetween('tanggal', [$dari, $sampai])
            ->sum('total');

        $totalTransaksi = Transaksi::where('jenis', 'penjualan')
            ->whereBetween('tanggal', [$dari, $sampai])
            ->count();

        $totalPembelian = 0;
        $labaBersih     = 0;

        if (auth()->user()->role == 'pemilik') {
            $totalPembelian = LaporanPembelian::whereBetween('tanggal', [$dari, $sampai])->sum('total');
            $labaBersih     = $totalPenjualan - $totalPembelian;
        }

        // Data tabel sesuai tab aktif
        if ($tab === 'pembelian' && auth()->user()->role == 'pemilik') {
            // Data pembelian dari tabel transaksi jenis pembelian
            $transaksi = Transaksi::with(['suplier', 'detail.produk'])
                ->where('jenis', 'pembelian')
                ->whereBetween('tanggal', [$dari, $sampai])
                ->latest('id_transaksi')
                ->paginate(10)
                ->appends(['dari' => $dari, 'sampai' => $sampai, 'tab' => $tab]);
        } else {
            // Data penjualan (default)
            $transaksi = Transaksi::with(['pelanggan', 'detail.produk'])
                ->where('jenis', 'penjualan')
                ->whereBetween('tanggal', [$dari, $sampai])
                ->latest('id_transaksi')
                ->paginate(10)
                ->appends(['dari' => $dari, 'sampai' => $sampai, 'tab' => $tab]);
        }

        $produkTerlaris = DB::table('detail_transaksi')
            ->join('transaksi', 'detail_transaksi.id_transaksi', '=', 'transaksi.id_transaksi')
            ->join('produk', 'detail_transaksi.id_produk', '=', 'produk.id_produk')
            ->where('transaksi.jenis', 'penjualan')
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
            'sampai',
            'tab'
        ));
    }

    public function exportExcel(Request $request)
    {
        if (auth()->user()->role !== 'pemilik') {
            abort(403, 'Anda tidak memiliki akses untuk mengekspor data.');
        }

        $dari   = $request->dari   ?? Carbon::now()->startOfMonth()->format('Y-m-d');
        $sampai = $request->sampai ?? Carbon::now()->endOfMonth()->format('Y-m-d');
        $tab    = $request->tab    ?? 'penjualan';

        if ($tab === 'pembelian') {
            $data     = Transaksi::with(['suplier', 'detail.produk'])
                ->where('jenis', 'pembelian')
                ->whereBetween('tanggal', [$dari, $sampai])
                ->latest('id_transaksi')
                ->get();
            $filename = 'laporan_pembelian_' . $dari . '_sd_' . $sampai . '.csv';
            $headers_csv = ['No', 'Tanggal', 'Supplier', 'Produk', 'Total', 'Keterangan'];
            $rows = $data->map(fn($t, $i) => [
                $i + 1,
                Carbon::parse($t->tanggal)->format('d/m/Y'),
                $t->suplier->nama_suplier ?? '-',
                $t->detail->first()->produk->nama_produk ?? '-',
                $t->total,
                $t->keterangan ?? '-',
            ]);
        } else {
            $data     = Transaksi::with(['pelanggan', 'detail.produk'])
                ->where('jenis', 'penjualan')
                ->whereBetween('tanggal', [$dari, $sampai])
                ->latest('id_transaksi')
                ->get();
            $filename = 'laporan_penjualan_' . $dari . '_sd_' . $sampai . '.csv';
            $headers_csv = ['No', 'Tanggal', 'Pelanggan', 'Produk', 'Total', 'Metode Pembayaran'];
            $rows = $data->map(fn($t, $i) => [
                $i + 1,
                Carbon::parse($t->tanggal)->format('d/m/Y'),
                $t->pelanggan->nama_pelanggan ?? '-',
                $t->detail->first()->produk->nama_produk ?? '-',
                $t->total,
                $t->metode_pembayaran ?? '-',
            ]);
        }

        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function() use ($rows, $headers_csv) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $headers_csv);
            foreach ($rows as $row) {
                fputcsv($file, $row);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}