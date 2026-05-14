<?php

namespace App\Http\Controllers;

use App\Models\Pembelian;
use App\Models\DetailPembelian;
use App\Models\Produk;
use App\Models\Suplier;
use App\Models\LaporanPembelian;
use Illuminate\Http\Request;

class PembelianController extends Controller
{
    public function index()
    {
        $pembelian = Pembelian::with(['suplier', 'detail.produk'])
            ->latest('id_pembelian')
            ->get();
        return view('pembelian.index', compact('pembelian'));
    }

    public function create()
    {
        $produk  = Produk::all();
        $suplier = Suplier::all();
        return view('pembelian.create', compact('produk', 'suplier'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'tanggal'           => 'required|date',
            'id_suplier'        => 'nullable|exists:suplier,id_suplier',
            'keterangan'        => 'nullable|string|max:500',
            'id_produk'         => 'required|array|min:1',
            'id_produk.*'       => 'required|exists:produk,id_produk',
            'jumlah.*'          => 'required|integer|min:1',
            'harga_beli.*'      => 'required|integer|min:0',
        ], [
            'tanggal.required'      => 'Tanggal wajib diisi.',
            'tanggal.date'          => 'Format tanggal tidak valid.',
            'id_suplier.exists'     => 'Suplier tidak ditemukan.',
            'id_produk.required'    => 'Minimal satu produk wajib dipilih.',
            'id_produk.min'         => 'Minimal satu produk wajib ditambahkan.',
            'jumlah.*.required'     => 'Jumlah wajib diisi.',
            'jumlah.*.min'          => 'Jumlah minimal 1.',
            'harga_beli.*.required' => 'Harga beli wajib diisi.',
            'harga_beli.*.min'      => 'Harga beli tidak boleh negatif.',
        ]);

        $total = 0;
        foreach ($request->id_produk as $i => $id_produk) {
            $total += $request->jumlah[$i] * $request->harga_beli[$i];
        }

        $pembelian = Pembelian::create([
            'tanggal'    => $request->tanggal,
            'id_suplier' => $request->id_suplier,
            'total'      => $total,
            'keterangan' => $request->keterangan,
        ]);

        foreach ($request->id_produk as $i => $id_produk) {
            $jumlah     = $request->jumlah[$i];
            $harga_beli = $request->harga_beli[$i];
            $subtotal   = $jumlah * $harga_beli;

            DetailPembelian::create([
                'id_pembelian' => $pembelian->id_pembelian,
                'id_produk'    => $id_produk,
                'jumlah'       => $jumlah,
                'harga_beli'   => $harga_beli,
                'subtotal'     => $subtotal,
            ]);

            // Pembelian dari suplier → masuk stok gudang (dalam dus)
            $produk = Produk::findOrFail($id_produk);
            $produk->stok_gudang += $jumlah;
            $produk->save();
        }

        LaporanPembelian::create([
            'id_pembelian' => $pembelian->id_pembelian,
            'tanggal'      => $pembelian->tanggal,
            'total'        => $pembelian->total,
        ]);

        return redirect()->route('pembelian.index')->with('success', 'Pembelian berhasil disimpan.');
    }

    public function show($id)
    {
        $pembelian = Pembelian::with(['suplier', 'detail.produk'])->findOrFail($id);
        return view('pembelian.show', compact('pembelian'));
    }

    public function destroy($id)
{
    $pembelian = Pembelian::findOrFail($id);
    $pembelian->delete();

    return redirect()->route('pembelian.index')->with('success', 'Pembelian berhasil dihapus.');
}
}