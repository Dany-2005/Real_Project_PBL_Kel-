@extends('layouts.app')

@section('content')
<div class="p-8">
    {{-- HEADER --}}
    <div class="mb-10 flex justify-between items-end">
        <div>
            <h2 class="text-3xl font-black text-[#2d6a4f] tracking-tight">Kustomatisasi Visual</h2>
            <p class="text-gray-400 text-sm font-medium italic">Kelola branding toko anda.</p>
        </div>
        {{-- Tombol Kembali hanya muncul kalau tidak di main menu --}}
        <button id="btn-back" onclick="backToMenu()" class="hidden flex items-center gap-2 mb-2 text-gray-500 hover:text-[#2d6a4f] font-bold text-sm transition-all bg-white px-4 py-2 rounded-xl shadow-sm border">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Kembali
        </button>
    </div>

    {{-- 1. DASHBOARD MENU (UTAMA) --}}
    <div id="main-menu" class="grid grid-cols-1 md:grid-cols-2 gap-8">
        <button onclick="openTab('welcome')" class="group p-10 bg-white rounded-[3rem] border-2 border-transparent hover:border-[#2d6a4f] shadow-sm hover:shadow-2xl transition-all text-left">
            <div class="w-16 h-16 bg-green-50 rounded-2xl flex items-center justify-center mb-6 group-hover:bg-[#2d6a4f] transition-colors">
                <svg class="w-8 h-8 text-[#2d6a4f] group-hover:text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
            </div>
            <h3 class="text-2xl font-black text-gray-800">Halaman Depan</h3>
            <p class="text-gray-400 text-sm mt-2">Atur teks dan manajemen slide (Max 10).</p>
        </button>

        <button onclick="openTab('login')" class="group p-10 bg-white rounded-[3rem] border-2 border-transparent hover:border-blue-500 shadow-sm hover:shadow-2xl transition-all text-left">
            <div class="w-16 h-16 bg-blue-50 rounded-2xl flex items-center justify-center mb-6 group-hover:bg-blue-500 transition-colors">
                <svg class="w-8 h-8 text-blue-500 group-hover:text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/></svg>
            </div>
            <h3 class="text-2xl font-black text-gray-800">Visual Login</h3>
            <p class="text-gray-400 text-sm mt-2">Sesuaikan logo dan subtitle.</p>
        </button>
    </div>

    {{-- 2. TAB KONTEN: HALAMAN DEPAN --}}
    <div id="welcome" class="tab-content hidden space-y-10">
        {{-- Form Teks --}}
        <form action="{{ route('landing.update') }}" method="POST" class="bg-white p-8 rounded-[2.5rem] border shadow-sm">
            @csrf
            <h4 class="font-black text-gray-800 uppercase tracking-tighter mb-6">1. Konten Teks</h4>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <div class="space-y-4">
                    <input type="text" name="judul_h1" value="{{ $data->judul_h1 ?? '' }}" placeholder="Judul H1" class="w-full p-4 bg-gray-50 rounded-2xl border outline-none">
                    <input type="text" name="judul_highlight" value="{{ $data->judul_highlight ?? '' }}" placeholder="Highlight Hijau" class="w-full p-4 bg-green-50 rounded-2xl border border-green-100 outline-none">
                </div>
                <textarea name="deskripsi" class="w-full p-4 bg-gray-50 rounded-2xl border outline-none h-full min-h-[120px]" placeholder="Deskripsi singkat...">{{ $data->deskripsi ?? '' }}</textarea>
            </div>
            <button type="submit" class="mt-6 bg-[#2d6a4f] text-white px-10 py-3 rounded-xl font-bold">Simpan Teks</button>
        </form>

        {{-- Manajemen Slide --}}
        <div>
            <h4 class="font-black text-gray-800 uppercase tracking-tighter mb-6 text-sm">2. Manajemen Slide (Max 10)</h4>
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                @foreach($data->slides as $index => $slide)
                <div class="bg-white p-4 rounded-[2rem] border shadow-sm relative group">
                    <div class="w-full h-40 bg-gray-100 rounded-2xl mb-4 overflow-hidden relative">
                        @if($slide->type == 'video')
                            <video src="{{ asset('storage/'.$slide->path) }}" class="w-full h-full object-cover" muted></video>
                        @else
                            <img src="{{ asset('storage/'.$slide->path) }}" class="w-full h-full object-cover">
                        @endif
                        <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 flex items-center justify-center transition-opacity">
                            <form action="{{ route('landing.slide.destroy', $slide->id) }}" method="POST">
                                @csrf @method('DELETE')
                                <button type="submit" class="p-2 bg-red-600 text-white rounded-lg">Hapus</button>
                            </form>
                        </div>
                    </div>
                </div>
                @endforeach

                @if($data->slides->count() < 10)
                <div class="border-2 border-dashed border-gray-200 p-4 rounded-[2rem] flex flex-col items-center justify-center min-h-[180px] bg-gray-50/50">
                    <form action="{{ route('landing.slide.store') }}" method="POST" enctype="multipart/form-data" class="text-center">
                        @csrf
                        <input type="hidden" name="order" value="{{ ($data->slides->max('order') ?? 0) + 1 }}">
                        <p class="text-[10px] font-black text-gray-800 uppercase mb-2">Tambah Slide</p>
                        <input type="file" name="file" onchange="this.form.submit()" class="text-[10px]">
                    </form>
                </div>
                @endif
            </div>
        </div>
    </div>

    {{-- 3. TAB KONTEN: VISUAL LOGIN --}}
    <div id="login" class="tab-content hidden space-y-10">
        <form action="{{ route('landing.update') }}" method="POST" enctype="multipart/form-data" class="bg-white p-8 rounded-[2.5rem] border shadow-sm">
            @csrf
            <h4 class="font-black text-gray-800 uppercase tracking-tighter mb-6">Pengaturan Visual Login</h4>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <div class="space-y-4">
                    <div>
                        <label class="text-[10px] font-bold uppercase text-gray-400">Judul Login</label>
                        <input type="text" name="login_title" value="{{ $data->login_title ?? '' }}" class="w-full p-4 bg-gray-50 rounded-2xl border outline-none">
                    </div>
                    <div>
                        <label class="text-[10px] font-bold uppercase text-gray-400">Subtitle</label>
                        <input type="text" name="login_subtitle" value="{{ $data->login_subtitle ?? '' }}" class="w-full p-4 bg-gray-50 rounded-2xl border outline-none">
                    </div>
                </div>
                <div class="space-y-4">
                    <div>
                        <label class="text-[10px] font-bold uppercase text-gray-400">Warna Tema (Hex)</label>
                        <input type="color" name="login_bg_color" value="{{ $data->login_bg_color ?? '#2d6a4f' }}" class="w-full h-14 p-1 bg-gray-50 rounded-2xl border outline-none">
                    </div>
                    <div>
                        <label class="text-[10px] font-bold uppercase text-gray-400">Logo Baru</label>
                        <input type="file" name="login_logo" class="w-full p-3 bg-gray-50 rounded-2xl border text-xs">
                    </div>
                </div>
            </div>
            <button type="submit" class="mt-8 bg-blue-600 text-white px-10 py-3 rounded-xl font-bold">Simpan Visual Login</button>
        </form>
    </div>
</div>

<script>
    function openTab(id) {
        // Sembunyikan Menu Utama
        document.getElementById('main-menu').classList.add('hidden');
        // Sembunyikan semua konten tab
        document.querySelectorAll('.tab-content').forEach(t => t.classList.add('hidden'));
        // Tampilkan tab yang dipilih
        document.getElementById(id).classList.remove('hidden');
        // Tampilkan tombol kembali
        document.getElementById('btn-back').classList.remove('hidden');
    }

    function backToMenu() {
        // Tampilkan Menu Utama
        document.getElementById('main-menu').classList.remove('hidden');
        // Sembunyikan semua konten tab
        document.querySelectorAll('.tab-content').forEach(t => t.classList.add('hidden'));
        // Sembunyikan tombol kembali
        document.getElementById('btn-back').classList.add('hidden');
    }
</script>
@endsection