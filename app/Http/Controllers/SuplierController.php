<?php

namespace App\Http\Controllers;

use App\Models\Suplier;
use Illuminate\Http\Request;

class SuplierController extends Controller
{
    public function index()
    {
        $suplier = Suplier::all();
        return view('suplier.index', compact('suplier'));
    }

    public function create()
    {
        return view('suplier.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_suplier' => 'required|string|max:255',
            'no_hp'        => [
                'required',
                'digits_between:10,15',
                'regex:/^(0|62|\+62)8[1-9][0-9]{7,10}$/',
                'unique:suplier,no_hp',
            ],
            'alamat'       => 'nullable|string',
        ], [
            'nama_suplier.required' => 'Nama suplier wajib diisi.',
            'no_hp.required'        => 'Nomor HP wajib diisi.',
            'no_hp.digits_between'  => 'Nomor HP harus berupa angka antara 10-15 digit.',
            'no_hp.regex'           => 'Format nomor HP tidak valid. Contoh: 081234567890.',
            'no_hp.unique'          => 'Nomor HP sudah terdaftar.',
        ]);

        Suplier::create($request->only('nama_suplier', 'no_hp', 'alamat'));

        return redirect('/suplier')->with('success', 'Data berhasil ditambahkan');
    }

    public function edit($id)
    {
        $suplier = Suplier::findOrFail($id);
        return view('suplier.edit', compact('suplier'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'nama_suplier' => 'required|string|max:255',
            'no_hp'        => [
                'required',
                'digits_between:10,15',
                'regex:/^(0|62|\+62)8[1-9][0-9]{7,10}$/',
                'unique:suplier,no_hp,' . $id . ',id_suplier',
            ],
            'alamat'       => 'nullable|string',
        ], [
            'nama_suplier.required' => 'Nama suplier wajib diisi.',
            'no_hp.required'        => 'Nomor HP wajib diisi.',
            'no_hp.digits_between'  => 'Nomor HP harus berupa angka antara 10-15 digit.',
            'no_hp.regex'           => 'Format nomor HP tidak valid. Contoh: 081234567890.',
            'no_hp.unique'          => 'Nomor HP sudah terdaftar.',
        ]);

        $suplier = Suplier::findOrFail($id);
        $suplier->update($request->only('nama_suplier', 'no_hp', 'alamat'));

        return redirect('/suplier')->with('success', 'Data berhasil diupdate');
    }

    public function destroy($id)
    {
        $suplier = Suplier::findOrFail($id);
        $suplier->delete();

        return redirect('/suplier')->with('success', 'Data berhasil dihapus');
    }
}