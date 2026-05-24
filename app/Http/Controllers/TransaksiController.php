<?php

namespace App\Http\Controllers;

use App\Models\Transaksi;
use App\Models\DetailTransaksi;
use App\Models\Produk;
use App\Models\Pelanggan;
use App\Models\Kategori;
use App\Models\Diskon;
use App\Models\Suplier;
use App\Models\LaporanPembelian;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class TransaksiController extends Controller
{
    // ==================== PENJUALAN ====================

    public function index()
    {
        $transaksi = Transaksi::with(['detail.produk', 'pelanggan'])
            ->where('jenis', 'penjualan')
            ->where('id_user', Auth::id())
            ->latest('id_transaksi')
            ->get();
        return view('transaksi.index', compact('transaksi'));
    }

    public function create()
    {
        $today = now()->toDateString();
        $produk = Produk::with(['kategori', 'diskon' => function($query) use ($today) {
            $query->where('is_aktif', true)
                ->where('mulai_tgl', '<=', $today)
                ->where('selesai_tgl', '>=', $today);
        }])->get();
        $kategori = Kategori::all();
        $pelanggan = Pelanggan::all();
        return view('transaksi.create', compact('produk', 'kategori', 'pelanggan'));
    }

    public function store(Request $request)
    {
        // Decode keranjang jika dikirim sebagai JSON string
        if (is_string($request->keranjang)) {
            $request->merge(['keranjang' => json_decode($request->keranjang, true)]);
        }

        // Validasi dasar
        $request->validate([
            'keranjang'             => 'required|array|min:1',
            'keranjang.*.id_produk' => 'required|exists:produk,id_produk',
            'keranjang.*.jumlah'    => 'required|integer|min:1',
            'keranjang.*.tipe'      => 'required|in:eceran,grosir',
            'metode_pembayaran'     => 'required|in:tunai,transfer,kartu',
            'bayar'                 => 'required|numeric|min:0',
        ]);

        $subtotalKeseluruhan = 0;
        $totalDiskon = 0;
        $items = [];

        foreach ($request->keranjang as $item) {
            $produk = Produk::findOrFail($item['id_produk']);
            $jumlah = (int) $item['jumlah'];
            $tipe   = $item['tipe'];

            $harga = ($tipe === 'grosir')
                ? ($produk->harga_grosir ?? $produk->harga_satuan)
                : $produk->harga_satuan;

            // Cek stok
            if ($tipe === 'grosir') {
                if ($jumlah > $produk->stok_gudang)
                    return back()->withErrors(['stok' => "Stok gudang {$produk->nama_produk} tidak mencukupi."])->withInput();
            } else {
                if ($jumlah > $produk->stok_toko)
                    return back()->withErrors(['stok' => "Stok toko {$produk->nama_produk} tidak mencukupi."])->withInput();
            }

            // Cek diskon aktif
            // FIX: nominal_diskon dihitung per-unit, lalu dikali jumlah untuk totalDiskon
            $nominalDiskonPerUnit = 0;
            $today = now()->toDateString();
            $diskonAktif = $produk->diskon()
                ->where('is_aktif', true)
                ->where('mulai_tgl', '<=', $today)
                ->where('selesai_tgl', '>=', $today)
                ->where(function($q) use ($tipe) {
                    $q->where('lokasi_berlaku', 'semua')
                      ->orWhere('lokasi_berlaku', $tipe === 'grosir' ? 'gudang' : 'toko');
                })
                ->where(function($q) use ($request) {
                    $q->whereNull('id_pelanggan')
                      ->orWhere('id_pelanggan', $request->id_pelanggan ?: null);
                })
                ->first();

            if ($diskonAktif) {
                $minimalBeli = $tipe === 'grosir'
                    ? ($diskonAktif->minimal_beli_grosir ?? 0)
                    : ($diskonAktif->minimal_beli ?? 0);
                if ($jumlah >= $minimalBeli) {
                    // FIX: hitung diskon per unit saja
                    $nominalDiskonPerUnit = round($harga * ($diskonAktif->besar_diskon / 100));
                }
            }

            // FIX: total diskon untuk item ini = diskon per unit * jumlah
            $nominalDiskonTotal   = $nominalDiskonPerUnit * $jumlah;
            $subtotalItem         = ($harga * $jumlah) - $nominalDiskonTotal;
            $subtotalKeseluruhan += ($harga * $jumlah);
            $totalDiskon         += $nominalDiskonTotal;

            $items[] = [
                'id_produk'      => $produk->id_produk,
                'produk_obj'     => $produk,
                'jumlah'         => $jumlah,
                'tipe'           => $tipe,
                'harga'          => $harga,
                // FIX: simpan per-unit supaya show.blade bisa tampil benar (nominal * jumlah)
                'nominal_diskon' => $nominalDiskonPerUnit,
                'subtotal'       => $subtotalItem,
            ];
        }

        $total     = $subtotalKeseluruhan - $totalDiskon;
        $bayar     = (int) $request->bayar;
        $kembalian = $bayar - $total;

        if ($bayar < $total)
            return back()->withErrors(['bayar' => 'Uang bayar tidak cukup.'])->withInput();

        DB::beginTransaction();
        try {
            $transaksi = Transaksi::create([
                'jenis'             => 'penjualan',
                'tanggal'           => now(),
                'id_user'           => Auth::id(),
                'id_pelanggan'      => $request->id_pelanggan ?: null,
                'subtotal'          => $subtotalKeseluruhan,
                'total_diskon'      => $totalDiskon,
                'total'             => $total,
                'bayar'             => $bayar,
                'kembalian'         => $kembalian,
                'metode_pembayaran' => $request->metode_pembayaran,
                'catatan'           => $request->catatan,
            ]);

            foreach ($items as $item) {
                DetailTransaksi::create([
                    'id_transaksi'   => $transaksi->id_transaksi,
                    'id_produk'      => $item['id_produk'],
                    'tipe'           => $item['tipe'],
                    'tipe_stok'      => $item['tipe'] === 'grosir' ? 'gudang' : 'toko',
                    'jumlah'         => $item['jumlah'],
                    'harga'          => $item['harga'],
                    'harga_beli'     => 0,
                    'nominal_diskon' => $item['nominal_diskon'], // per unit
                    'subtotal'       => $item['subtotal'],
                ]);

                $p = $item['produk_obj'];
                if ($item['tipe'] === 'grosir') {
                    $p->stok_gudang -= $item['jumlah'];
                } else {
                    $p->stok_toko -= $item['jumlah'];
                }
                $p->save();
            }

            DB::commit();
            return redirect()->route('transaksi.show', $transaksi->id_transaksi)
                ->with('success', 'Transaksi Berhasil!');
        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage())->withInput();
        }
    }

    public function show($id)
    {
        $transaksi = Transaksi::with(['detail.produk', 'pelanggan', 'kasir'])
            ->where('jenis', 'penjualan')
            ->findOrFail($id);
        return view('transaksi.show', compact('transaksi'));
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $transaksi = Transaksi::with('detail')->findOrFail($id);
            foreach ($transaksi->detail as $detail) {
                $produk = Produk::find($detail->id_produk);
                if ($produk) {
                    if ($detail->tipe === 'grosir') {
                        $produk->stok_gudang += $detail->jumlah;
                    } else {
                        $produk->stok_toko += $detail->jumlah;
                    }
                    $produk->save();
                }
            }
            $transaksi->delete();
            DB::commit();
            return redirect()->route('transaksi.index')->with('success', 'Transaksi berhasil dihapus.');
        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Gagal menghapus transaksi.');
        }
    }

    // ==================== PEMBELIAN ====================

    public function indexPembelian()
    {
        $pembelian = Transaksi::with(['detail.produk', 'suplier'])
            ->where('jenis', 'pembelian')
            ->latest('id_transaksi')
            ->get();
        return view('pembelian.index', compact('pembelian'));
    }

    public function createPembelian()
    {
        $produk  = Produk::with('kategori')->get();
        $suplier = Suplier::all();
        return view('pembelian.create', compact('produk', 'suplier'));
    }

    public function storePembelian(Request $request)
    {
        $request->validate([
            'tanggal'      => 'required|date',
            'id_suplier'   => 'nullable|exists:suplier,id_suplier',
            'keterangan'   => 'nullable|string|max:500',
            'id_produk'    => 'required|array|min:1',
            'id_produk.*'  => 'required|exists:produk,id_produk',
            'jumlah.*'     => 'required|integer|min:1',
            'harga_beli.*' => 'required|integer|min:0',
        ]);

        $total = 0;
        foreach ($request->id_produk as $i => $id_produk) {
            $total += $request->jumlah[$i] * $request->harga_beli[$i];
        }

        DB::beginTransaction();
        try {
            $transaksi = Transaksi::create([
                'jenis'       => 'pembelian',
                'tanggal'     => $request->tanggal,
                'id_user'     => Auth::id(),
                'id_suplier'  => $request->id_suplier,
                'total'       => $total,
                'keterangan'  => $request->keterangan,
            ]);

            foreach ($request->id_produk as $i => $id_produk) {
                $jumlah     = $request->jumlah[$i];
                $harga_beli = $request->harga_beli[$i];
                $subtotal   = $jumlah * $harga_beli;

                DetailTransaksi::create([
                    'id_transaksi' => $transaksi->id_transaksi,
                    'id_produk'    => $id_produk,
                    'tipe_stok'    => 'gudang',
                    'jumlah'       => $jumlah,
                    'harga_beli'   => $harga_beli,
                    'harga'        => 0,
                    'subtotal'     => $subtotal,
                ]);

                $produk = Produk::findOrFail($id_produk);
                $produk->stok_gudang += $jumlah;
                $produk->save();
            }

            LaporanPembelian::create([
                'id_pembelian' => $transaksi->id_transaksi,
                'tanggal'      => $transaksi->tanggal,
                'total'        => $transaksi->total,
            ]);

            DB::commit();
            return redirect()->route('pembelian.index')->with('success', 'Pembelian berhasil disimpan.');
        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function showPembelian($id)
    {
        $pembelian = Transaksi::with(['detail.produk', 'suplier'])
            ->where('jenis', 'pembelian')
            ->findOrFail($id);
        return view('pembelian.show', compact('pembelian'));
    }

    public function destroyPembelian($id)
    {
        DB::beginTransaction();
        try {
            $pembelian = Transaksi::with('detail')->findOrFail($id);
            foreach ($pembelian->detail as $detail) {
                $produk = Produk::find($detail->id_produk);
                if ($produk) {
                    $produk->stok_gudang -= $detail->jumlah;
                    $produk->save();
                }
            }
            $pembelian->delete();
            DB::commit();
            return redirect()->route('pembelian.index')->with('success', 'Pembelian berhasil dihapus.');
        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Gagal menghapus pembelian.');
        }
    }
}