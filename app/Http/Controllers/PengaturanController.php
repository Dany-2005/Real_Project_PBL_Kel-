<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class PengaturanController extends Controller
{
    public function pemilikIndex()
    {
        $user = auth()->user();
        return view('pengaturan.pemilik', compact('user'));
    }

    public function pemilikUpdate(Request $request)
    {
        $user = auth()->user();

        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email,' . $user->id,
            'password' => 'nullable|min:8|confirmed',
        ], [
            'email.unique'        => 'Email ini sudah digunakan oleh akun lain.',
            'password.confirmed'  => 'Konfirmasi password tidak cocok.',
            'password.min'        => 'Password minimal harus 8 karakter.',
        ]);

        $user->name  = $request->name;
        $user->email = $request->email;

        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        $user->save();

        return back()->with('success', 'Profil pemilik berhasil diperbarui!');
    }

    public function kasir()
    {
        $kasir = User::where('role', 'kasir')->get();
        return view('pengaturan.kasir', compact('kasir'));
    }

    public function kasirStore(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users',
            'password' => 'required|min:8',
        ]);

        User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
            'role'     => 'kasir',
        ]);

        return back()->with('success', 'Akun kasir berhasil ditambahkan!');
    }

        public function kasirEdit($id)
    {
        $kasir = User::findOrFail($id);
        return view('pengaturan.kasir-edit', compact('kasir'));
    }

    public function kasirUpdate(Request $request, $id)
    {
        $kasir = User::findOrFail($id);

        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email,' . $id,
            'password' => 'nullable|min:8|confirmed', // ← tambah confirmed
        ], [
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
            'password.min'       => 'Password minimal 8 karakter.',
            'email.unique'       => 'Email sudah digunakan.',
        ]);

        $kasir->name  = $request->name;
        $kasir->email = $request->email;

        if ($request->filled('password')) {
            $kasir->password = Hash::make($request->password);
        }

        $kasir->save();

        return redirect()->route('pengaturan.kasir')->with('success', 'Akun kasir berhasil diperbarui!'); // ← fix redirect
    }

    public function kasirDestroy($id)
    {
        $kasir = User::findOrFail($id);
        $kasir->delete();

        return back()->with('success', 'Akun kasir berhasil dihapus!');
    }
}