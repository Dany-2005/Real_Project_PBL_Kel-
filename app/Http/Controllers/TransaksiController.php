<?php

namespace App\Http\Controllers;

use App\Models\Transaksi;
use App\Models\DetailTransaksi;
use App\Models\Produk;
use App\Models\Pelanggan;
use App\Models\Kategori;
use App\Models\Diskon;
use Illuminate\Http\Request;
use Carbon\Carbon;

class TransaksiController extends Controller
{
    public function index()
    {
        $transaksi = Transaksi::with(['detail.produk', 'pelanggan'])
            ->latest('id_transaksi')
            ->get();
        return view('transaksi.index', compact('transaksi'));
    }

    public function create()
    {
        $produk    = Produk::with(['kategori'])->get();
        $kategori  = Kategori::all();
        $pelanggan = Pelanggan::all();
        return view('transaksi.create', compact('produk', 'kategori', 'pelanggan'));
    }

    public function store(Request $request)
    {
        // Decode keranjang dari JSON string ke array
        if (is_string($request->keranjang)) {
            $request->merge([
                'keranjang' => json_decode($request->keranjang, true)
            ]);
        }

        $request->validate([
            'keranjang'             => 'required|array|min:1',
            'keranjang.*.id_produk' => 'required|exists:produk,id_produk',
            'keranjang.*.jumlah'    => 'required|integer|min:1',
            'keranjang.*.tipe'      => 'required|in:eceran,grosir',
            'metode_pembayaran'     => 'required|in:tunai,transfer,kartu',
            'bayar'                 => 'required|integer|min:0',
        ]);

        $today               = Carbon::today()->toDateString();
        $subtotalKeseluruhan = 0;
        $totalDiskon         = 0;
        $items               = [];

        foreach ($request->keranjang as $item) {
            $produk = Produk::findOrFail($item['id_produk']);
            $jumlah = $item['jumlah'];
            $tipe   = $item['tipe'];

            // Tentukan harga berdasarkan tipe
            $harga = $tipe === 'grosir'
                ? ($produk->harga_grosir ?? $produk->harga_satuan)
                : $produk->harga_satuan;

            // Cek diskon aktif untuk produk ini
            $diskon = Diskon::where('is_aktif', true)
                ->where('mulai_tgl', '<=', $today)
                ->where('selesai_tgl', '>=', $today)
                ->whereHas('produk', fn($q) => $q->where('produk.id_produk', $produk->id_produk))
                ->where('minimal_beli', '<=', $jumlah)
                ->first();

            $nominalDiskon = 0;
            if ($diskon) {
                $nominalDiskon = round(($harga * $jumlah) * $diskon->besar_diskon / 100);
            }

            $subtotal             = ($harga * $jumlah) - $nominalDiskon;
            $subtotalKeseluruhan += $harga * $jumlah;
            $totalDiskon         += $nominalDiskon;

            $items[] = [
                'produk'         => $produk,
                'jumlah'         => $jumlah,
                'tipe'           => $tipe,
                'harga'          => $harga,
                'nominal_diskon' => $nominalDiskon,
                'subtotal'       => $subtotal,
            ];
        }

        $total     = $subtotalKeseluruhan - $totalDiskon;
        $bayar     = (int) $request->bayar;
        $kembalian = $bayar - $total;

        // Setelah hitung $total, sebelum simpan transaksi
if ($request->bayar < $total) {
    return back()->withErrors(['bayar' => 'Jumlah bayar tidak boleh kurang dari total transaksi.'])->withInput();
}

foreach ($items as $item) {
    if ($item['tipe'] === 'grosir') {
        if ($item['jumlah'] > $item['produk']->stok_gudang) {
            return back()->withErrors([
                'stok' => "Stok gudang {$item['produk']->nama_produk} tidak cukup! Tersedia: {$item['produk']->stok_gudang} pcs"
            ])->withInput();
        }
    } else {
        if ($item['jumlah'] > $item['produk']->stok_toko) {
            return back()->withErrors([
                'stok' => "Stok toko {$item['produk']->nama_produk} tidak cukup! Tersedia: {$item['produk']->stok_toko} pcs"
            ])->withInput();
        }
    }
}

        // Simpan transaksi
        $transaksi = Transaksi::create([
            'tanggal'           => now(),
            'id_user'           => auth()->id(),
            'id_pelanggan'      => $request->id_pelanggan ?: null,
            'subtotal'          => $subtotalKeseluruhan,
            'total_diskon'      => $totalDiskon,
            'total'             => $total,
            'bayar'             => $bayar,
            'kembalian'         => $kembalian,
            'metode_pembayaran' => $request->metode_pembayaran,
            'catatan'           => $request->catatan,
        ]);

        // Simpan detail transaksi + kurangi stok
        foreach ($items as $item) {
            DetailTransaksi::create([
                'id_transaksi'   => $transaksi->id_transaksi,
                'id_produk'      => $item['produk']->id_produk,
                'tipe'           => $item['tipe'],
                'jumlah'         => $item['jumlah'],
                'harga'          => $item['harga'],
                'nominal_diskon' => $item['nominal_diskon'],
                'subtotal'       => $item['subtotal'],
            ]);

            // Kurangi stok sesuai tipe transaksi
            if ($item['tipe'] === 'grosir') {
                $item['produk']->stok_gudang -= $item['jumlah'];
            } else {
                $item['produk']->stok_toko -= $item['jumlah'];
            }
            $item['produk']->save();
        }

        return redirect()->route('transaksi.show', $transaksi->id_transaksi)
            ->with('success', 'Transaksi berhasil disimpan!');
    }

    public function show($id)
    {
        $transaksi = Transaksi::with(['detail.produk', 'pelanggan', 'kasir'])->findOrFail($id);
        return view('transaksi.show', compact('transaksi'));
    }

    public function destroy($id)
    {
        $transaksi = Transaksi::with('detail')->findOrFail($id);

        // Kembalikan stok
        foreach ($transaksi->detail as $detail) {
            $produk = Produk::findOrFail($detail->id_produk);
            if ($detail->tipe === 'grosir') {
                $produk->stok_gudang += $detail->jumlah;
            } else {
                $produk->stok_toko += $detail->jumlah;
            }
            $produk->save();
        }

        $transaksi->delete();
        return redirect()->route('transaksi.index')
            ->with('success', 'Transaksi berhasil dihapus.');
    }

    
}