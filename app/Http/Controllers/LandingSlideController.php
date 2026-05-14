<?php

namespace App\Http\Controllers;

use App\Models\LandingPage;
use App\Models\LandingSlide;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class LandingSlideController extends Controller
{
    // Fungsi buat tambah slide baru
    public function store(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:jpg,jpeg,png,mp4,mov,ogg|max:20480', // Max 20MB
        ]);

        // Ambil ID Landing Page (asumsi cuma ada 1 data landing page)
        $landing = LandingPage::first();

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $mime = $file->getMimeType();
            
            // Cek apakah file itu video atau gambar
            $type = str_contains($mime, 'video') ? 'video' : 'image';
            
            // Simpan file ke folder storage/app/public/landing/slides
            $path = $file->store('landing/slides', 'public');

            // Simpan datanya ke tabel landing_slides
            LandingSlide::create([
                'landing_page_id' => $landing->id,
                'path' => $path,
                'type' => $type,
                'order' => LandingSlide::where('landing_page_id', $landing->id)->count() + 1
            ]);

            return back()->with('success', 'Slide baru berhasil ditambahkan!');
        }

        return back()->with('error', 'Gagal mengupload file.');
    }

    // Fungsi buat hapus slide
    public function destroy($id)
    {
        $slide = LandingSlide::findOrFail($id);

        // 1. Hapus file fisiknya dari folder storage agar tidak menumpuk
        if (Storage::disk('public')->exists($slide->path)) {
            Storage::disk('public')->delete($slide->path);
        }

        // 2. Hapus data dari database
        $slide->delete();

        return back()->with('success', 'Slide berhasil dihapus!');
    }
}