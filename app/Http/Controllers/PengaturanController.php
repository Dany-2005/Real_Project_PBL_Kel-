<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Suplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class PengaturanController extends Controller
{
    // ==========================================
    // 1. FITUR AKUN PEMILIK (Update Profil Sendiri)
    // ==========================================
   public function pemilikIndex()
{
    $user = auth()->user(); 
    return view('pengaturan.pemilik', compact('user'));
}


    public function pemilikUpdate(Request $request)
    {
        $user = auth()->user();

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'password' => 'nullable|min:8|confirmed',
        ], [
            'email.unique' => 'Email ini sudah digunakan oleh akun lain.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
            'password.min' => 'Password minimal harus 8 karakter.',
        ]);

        $user->name = $request->name;
        $user->email = $request->email;

        // Hanya update password jika input password diisi
        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        $user->save();

        return back()->with('success', 'Profil pemilik berhasil diperbarui!');
    }

    // ==========================================
    // 2. FITUR AKUN KASIR (Kelola Staff)
    // ==========================================
        public function kasir()
    {
        // Ambil data dengan nama variabel $kasir
        $kasir = User::where('role', 'kasir')->get();
        
        // Kirim ke view dengan nama string yang SAMA ('kasir')
        return view('pengaturan.kasir', compact('kasir'));
    }
    public function kasirStore(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8',
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'kasir',
        ]);

        return back()->with('success', 'Akun kasir berhasil ditambahkan!');
    }

    public function kasirUpdate(Request $request, $id)
    {
        $kasir = User::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $id,
            'password' => 'nullable|min:8',
        ]);

        $kasir->name = $request->name;
        $kasir->email = $request->email;
        
        if ($request->filled('password')) {
            $kasir->password = Hash::make($request->password);
        }

        $kasir->save();

        return back()->with('success', 'Akun kasir berhasil diperbarui!');
    }

    public function kasirDestroy($id)
    {
        $kasir = User::findOrFail($id);
        $kasir->delete();

        return back()->with('success', 'Akun kasir berhasil dihapus!');
    }

    // ==========================================
    // 3. FITUR AKUN SUPLIER (Kelola Mitra)
    // ==========================================
public function suplier()
{
    $supliers = Suplier::all();
    return view('pengaturan.suplier', compact('supliers'));
}

    public function suplierStore(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'alamat' => 'required',
            'telepon' => 'required|string|max:15',
        ]);

        Suplier::create($request->all());

        return back()->with('success', 'Data suplier berhasil ditambahkan!');
    }

    public function suplierUpdate(Request $request, $id)
    {
        $suplier = Suplier::findOrFail($id);
        $suplier->update($request->all());

        return back()->with('success', 'Data suplier berhasil diperbarui!');
    }

    public function suplierDestroy($id)
    {
        $suplier = Suplier::findOrFail($id);
        $suplier->delete();

        return back()->with('success', 'Data suplier berhasil dihapus!');
    }
}