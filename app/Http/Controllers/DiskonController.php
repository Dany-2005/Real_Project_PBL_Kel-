<?php

namespace App\Http\Controllers;

use App\Models\Diskon;
use App\Models\Produk;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\Pelanggan;

class DiskonController extends Controller
{
    public function index()
    {
        $diskon = Diskon::with('produk')->latest('id_diskon')->get();
        return view('diskon.index', compact('diskon'));
    }

public function create()
    {
        $produk = Produk::with('kategori')->get();
        $pelanggan = Pelanggan::all(); // <--- Tambahkan ini
        return view('diskon.create', compact('produk', 'pelanggan')); // <--- Pastikan 'pelanggan' ada di compact
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_diskon'  => 'required|string|max:150',
            'besar_diskon' => 'required|integer|min:1|max:100',
            'minimal_beli' => 'required|integer|min:1',
            'mulai_tgl'    => 'required|date',
            'selesai_tgl'  => 'required|date|after_or_equal:mulai_tgl',
            'id_produk'    => 'required|array|min:1',
            'id_produk.*'  => 'exists:produk,id_produk',
        ], [
            'nama_diskon.required'  => 'Nama diskon wajib diisi.',
            'besar_diskon.required' => 'Besar diskon wajib diisi.',
            'minimal_beli.required' => 'Minimal beli wajib diisi.',
            'mulai_tgl.required'    => 'Tanggal mulai wajib diisi.',
            'selesai_tgl.required'  => 'Tanggal selesai wajib diisi.',
            'selesai_tgl.after_or_equal' => 'Tanggal selesai tidak boleh sebelum tanggal mulai.',
            'id_produk.required'    => 'Minimal satu produk wajib dipilih.',
        ]);

        $diskon = Diskon::create([
            'nama_diskon'  => $request->nama_diskon,
            'besar_diskon' => $request->besar_diskon,
            'minimal_beli' => $request->minimal_beli,
            'mulai_tgl'    => $request->mulai_tgl,
            'selesai_tgl'  => $request->selesai_tgl,
            'is_aktif'     => $request->has('is_aktif'),
        ]);

        $diskon->produk()->sync($request->id_produk);

        return redirect()->route('diskon.index')->with('success', 'Diskon berhasil ditambahkan.');
    }

public function edit($id)
    {
        $diskon = Diskon::with('produk')->findOrFail($id);
        $produk = Produk::with('kategori')->get();
        $pelanggan = Pelanggan::all(); // <--- Tambahkan ini
        $produkTerpilih = $diskon->produk->pluck('id_produk')->toArray();
        return view('diskon.edit', compact('diskon', 'produk', 'produkTerpilih', 'pelanggan')); // <--- Pastikan 'pelanggan' ada
    }

    public function update(Request $request, $id)
    {
        $diskon = Diskon::findOrFail($id);

        $request->validate([
            'nama_diskon'  => 'required|string|max:150',
            'besar_diskon' => 'required|integer|min:1|max:100',
            'minimal_beli' => 'required|integer|min:1',
            'mulai_tgl'    => 'required|date',
            'selesai_tgl'  => 'required|date|after_or_equal:mulai_tgl',
            'id_produk'    => 'required|array|min:1',
            'id_produk.*'  => 'exists:produk,id_produk',
        ], [
            'nama_diskon.required'  => 'Nama diskon wajib diisi.',
            'besar_diskon.required' => 'Besar diskon wajib diisi.',
            'minimal_beli.required' => 'Minimal beli wajib diisi.',
            'mulai_tgl.required'    => 'Tanggal mulai wajib diisi.',
            'selesai_tgl.required'  => 'Tanggal selesai wajib diisi.',
            'selesai_tgl.after_or_equal' => 'Tanggal selesai tidak boleh sebelum tanggal mulai.',
            'id_produk.required'    => 'Minimal satu produk wajib dipilih.',
        ]);

        $diskon->update([
            'nama_diskon'  => $request->nama_diskon,
            'besar_diskon' => $request->besar_diskon,
            'minimal_beli' => $request->minimal_beli,
            'mulai_tgl'    => $request->mulai_tgl,
            'selesai_tgl'  => $request->selesai_tgl,
            'is_aktif'     => $request->has('is_aktif'),
        ]);

        $diskon->produk()->sync($request->id_produk);

        return redirect()->route('diskon.index')->with('success', 'Diskon berhasil diupdate.');
    }

    public function destroy($id)
    {
        Diskon::destroy($id);
        return back()->with('success', 'Diskon berhasil dihapus.');
    }
}