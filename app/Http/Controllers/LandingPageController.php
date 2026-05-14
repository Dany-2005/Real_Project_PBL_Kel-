<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\LandingPage;
use App\Models\LandingSlide;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class LandingPageController extends Controller
{
    public function index()
    {
        // Pake firstOrCreate biar kalau DB fresh, minimal ada 1 baris data biar gak error
        $data = LandingPage::with('slides')->first() ?? LandingPage::create([
            'judul_h1' => 'Solusi Belanja',
            'judul_highlight' => 'Lengkap'
        ]);
        
        return view('Pengaturan.landing', compact('data'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'judul_h1' => 'nullable|string|max:255',
            'judul_highlight' => 'nullable|string|max:255',
            'deskripsi' => 'nullable|string',
            'login_bg_color' => 'nullable|string|max:10',
            'login_text_color' => 'nullable|string|max:10',
            'login_title' => 'nullable|string|max:255',
            'login_subtitle' => 'nullable|string|max:255',
            'login_font_family' => 'nullable|string',
            'login_logo' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'login_icon' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:1024',
        ]);

        $landing = LandingPage::first() ?? new LandingPage();
        $landing->fill($request->only([
            'judul_h1', 'judul_highlight', 'deskripsi', 
            'login_bg_color', 'login_text_color', 
            'login_title', 'login_subtitle', 'login_font_family'
        ]));
        $landing->save();

        if ($request->hasFile('login_logo')) {
            if ($landing->login_logo_path) Storage::disk('public')->delete($landing->login_logo_path);
            $landing->login_logo_path = $request->file('login_logo')->store('landing/login', 'public');
        }

        if ($request->hasFile('login_icon')) {
            if ($landing->login_icon_path) Storage::disk('public')->delete($landing->login_icon_path);
            $landing->login_icon_path = $request->file('login_icon')->store('landing/login', 'public');
        }

        $landing->save();
        return back()->with('success', 'Pengaturan diperbarui!');
    }

    public function storeSlide(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:jpg,jpeg,png,webp,mp4,mov|max:20480',
            'order' => 'required|integer'
        ]);

        $landing = LandingPage::first() ?? LandingPage::create(['judul_h1' => 'Solusi Belanja']);

        // Fitur Ganti Media: Hapus yang lama di slot yang sama
        $oldSlide = LandingSlide::where('landing_page_id', $landing->id)
                                ->where('order', $request->order)->first();
        if ($oldSlide) {
            Storage::disk('public')->delete($oldSlide->path);
            $oldSlide->delete();
        }

        $file = $request->file('file');
        $type = Str::startsWith($file->getMimeType(), 'video') ? 'video' : 'image';
        $path = $file->store('landing/slides', 'public');

        LandingSlide::create([
            'landing_page_id' => $landing->id,
            'path' => $path,
            'type' => $type,
            'order' => $request->order
        ]);

        return back()->with('success', "Slide {$request->order} berhasil diupdate!");
    }

    public function destroySlide($id)
    {
        $slide = LandingSlide::findOrFail($id);
        if (Storage::disk('public')->exists($slide->path)) {
            Storage::disk('public')->delete($slide->path);
        }
        $slide->delete();
        return back()->with('success', 'Slide dihapus!');
    }
}