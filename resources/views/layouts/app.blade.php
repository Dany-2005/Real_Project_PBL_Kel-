<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Sarana Agro Makmur') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <script src="https://cdn.tailwindcss.com"></script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    @php
        $landing = \App\Models\LandingPage::first();
        $brandColor = $landing->login_text_color ?? '#2d6a4f';
        $title      = $landing->login_title ?? 'Sarana Agro Makmur';
        $logoPath   = ($landing && $landing->login_logo_path) ? asset('storage/' . $landing->login_logo_path) : asset('images/logotoko.png');
    @endphp
</head>
<body class="font-sans antialiased bg-gray-100">

<div class="flex h-screen overflow-hidden" x-data="{ openPengaturan: {{ request()->is('pengaturan*') || request()->routeIs('landing.*') ? 'true' : 'false' }} }">
    {{-- SIDEBAR --}}
    <aside class="w-64 bg-white flex flex-col justify-between shadow-md flex-shrink-0">
        <div>
            {{-- LOGO HEADER SIDEBAR --}}
            <div class="flex items-center gap-3 px-6 py-5 border-b border-gray-200">
                <img src="{{ $logoPath }}" alt="Logo" class="w-10 h-10 object-contain">
                <div>
                    <p class="font-bold text-[{{ $brandColor }}] text-sm leading-tight">{{ $title }}</p>
                    <p class="text-xs text-gray-500 capitalize">{{ auth()->user()->role }}</p>
                </div>
            </div>

            <nav class="mt-4 px-4 space-y-1">
                {{-- MENU UMUM --}}
                <a href="{{ route('dashboard') }}"
                   class="flex items-center gap-3 px-4 py-3 rounded-xl font-bold text-sm transition
                   {{ request()->routeIs('dashboard') ? 'bg-[#2d6a4f] text-white' : 'text-gray-700 hover:bg-green-50' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                    Dashboard
                </a>

                <a href="{{ route('laporan.index') }}"
                   class="flex items-center gap-3 px-4 py-3 rounded-xl font-bold text-sm transition
                   {{ request()->routeIs('laporan.*') ? 'bg-[#2d6a4f] text-white' : 'text-gray-700 hover:bg-green-50' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 2v-6m-9-3h12a2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5a2 2 0 012-2z"/></svg>
                    Laporan
                </a>

                {{-- MENU KHUSUS PEMILIK --}}
                @if(auth()->user()->role === 'pemilik')
                    <div class="pt-4 pb-1 px-4">
                        <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Master Data</p>
                    </div>

                    <a href="{{ route('pelanggan.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl font-bold text-sm transition {{ request()->routeIs('pelanggan.*') ? 'bg-[#2d6a4f] text-white' : 'text-gray-700 hover:bg-green-50' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                        Pelanggan
                    </a>
                    <a href="{{ route('produk.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl font-bold text-sm transition {{ request()->routeIs('produk.*') ? 'bg-[#2d6a4f] text-white' : 'text-gray-700 hover:bg-green-50' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                        Produk
                    </a>
                    <a href="{{ route('kategori.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl font-bold text-sm transition {{ request()->routeIs('kategori.*') ? 'bg-[#2d6a4f] text-white' : 'text-gray-700 hover:bg-green-50' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 11h.01M7 15h.01M13 7h.01M13 11h.01M13 15h.01M17 7h.01M17 11h.01M17 15h.01M5 21h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                        Kategori
                    </a>
                    <a href="{{ route('diskon.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl font-bold text-sm transition {{ request()->routeIs('diskon.*') ? 'bg-[#2d6a4f] text-white' : 'text-gray-700 hover:bg-green-50' }}">
                        {{-- Icon Tag Diskon --}}
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                        Diskon
                    </a>

                    <a href="{{ route('pembelian.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl font-bold text-sm transition {{ request()->routeIs('pembelian.*') ? 'bg-[#2d6a4f] text-white' : 'text-gray-700 hover:bg-green-50' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                        Pembelian
                    </a>
                  
                    {{-- Submenu Pengaturan (Landing Page Pindah Sini) --}}
                    <div class="pt-2">
                        <button @click="openPengaturan = !openPengaturan" 
                                class="w-full flex items-center justify-between gap-3 px-4 py-3 rounded-xl font-bold text-sm transition 
                                {{ request()->is('pengaturan*') || request()->routeIs('landing.*') ? 'bg-[#2d6a4f] text-white' : 'text-gray-700 hover:bg-green-50' }}">
                            <div class="flex items-center gap-3">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                <span>Pengaturan</span>
                            </div>
                            <svg class="w-4 h-4 transition-transform" :class="openPengaturan ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </button>
                        
                        <div x-show="openPengaturan" x-cloak x-transition class="ml-9 mt-1 space-y-1 border-l-2 border-gray-100">
                            <a href="{{ route('landing.index') }}" class="block px-4 py-2 rounded-xl text-sm font-bold {{ request()->routeIs('landing.*') ? 'text-[#2d6a4f] bg-green-50' : 'text-gray-500 hover:text-[#2d6a4f]' }}">Landing Page</a>
                            <a href="{{ route('pengaturan.pemilik') }}" class="block px-4 py-2 rounded-xl text-sm font-bold {{ request()->is('pengaturan/pemilik') ? 'text-[#2d6a4f] bg-green-50' : 'text-gray-500 hover:text-[#2d6a4f]' }}">Akun Pemilik</a>
                            <a href="{{ route('pengaturan.kasir') }}" class="block px-4 py-2 rounded-xl text-sm font-bold {{ request()->is('pengaturan/kasir') ? 'text-[#2d6a4f] bg-green-50' : 'text-gray-500 hover:text-[#2d6a4f]' }}">Akun Kasir</a>
                            <a href="{{ route('pengaturan.suplier') }}" class="block px-4 py-2 rounded-xl text-sm font-bold {{ request()->is('pengaturan/suplier') ? 'text-[#2d6a4f] bg-green-50' : 'text-gray-500 hover:text-[#2d6a4f]' }}">Akun Suplier</a>
                        </div>
                    </div>
                @endif

                {{-- MENU KHUSUS KASIR --}}
                @if(auth()->user()->role === 'kasir')
                    <div class="pt-4 pb-1 px-4">
                        <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Operasional</p>
                    </div>
                    <a href="{{ route('transaksi.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl font-bold text-sm transition {{ request()->routeIs('transaksi.*') ? 'bg-[#2d6a4f] text-white' : 'text-gray-700 hover:bg-green-50' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                        Transaksi
                    </a>
                @endif
            </nav>
        </div>

        {{-- Profil & Logout --}}
        <div class="px-4 py-4 border-t border-gray-200">
            <div class="flex items-center gap-3 px-3 py-2 rounded-xl hover:bg-green-50 transition">
                <div class="w-9 h-9 rounded-full bg-[#2d6a4f] flex items-center justify-center text-white font-bold text-sm">
                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-bold text-gray-800 truncate">{{ auth()->user()->name }}</p>
                    <p class="text-xs text-gray-500 truncate">{{ auth()->user()->email }}</p>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="p-2 hover:bg-red-50 rounded-lg transition group">
                        <svg class="w-5 h-5 text-gray-400 group-hover:text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                    </button>
                </form>
            </div>
        </div>
    </aside>

    {{-- CONTENT AREA --}}
    <div class="flex-1 flex flex-col overflow-hidden">
        <header class="bg-white shadow-sm px-6 py-4 border-b border-gray-100">
            @isset($header) 
                <div class="font-bold text-xl text-gray-800">
                    {{ $header }}
                </div>
            @endisset
        </header>
        <main class="flex-1 overflow-y-auto p-6 bg-gray-50/50">
            @if(isset($slot))
                {{ $slot }}
            @else
                @yield('content')
            @endif
        </main>
    </div>
</div>

<script src="//unpkg.com/alpinejs" defer></script>
</body>
</html>