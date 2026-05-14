<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name', 'Sarana Agro Makmur') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
    
    <style>
        /* 1. PRELOADER */
        #preloader {
            position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background-color: white; display: flex; flex-direction: column;
            justify-content: center; align-items: center; z-index: 9999;
            transition: opacity 0.5s ease;
        }
        .trolley-wrap { position: relative; width: 250px; height: 60px; overflow: hidden; border-bottom: 2px solid #f0f0f0; }
        .trolley-run { font-size: 40px; position: absolute; bottom: 0; transform: scaleX(-1); animation: moveRight 2s infinite linear; }
        @keyframes moveRight { 0% { left: -50px; } 100% { left: 100%; } }

        /* 2. SLIDER SETTINGS */
        .swiper { width: 100%; height: 500px; border-radius: 3rem; background: #fff; }
        .swiper-slide video, .swiper-slide img { width: 100%; height: 100%; object-fit: cover; }
        .swiper-button-next, .swiper-button-prev { color: #2d6a4f !important; transform: scale(0.6); }
        .swiper-pagination-bullet-active { background: #2d6a4f !important; }
    </style>
</head>
<body class="bg-white font-sans text-gray-900 overflow-x-hidden">

    @php
        // Ambil data pertama, jika tidak ada buat objek kosong agar tidak error property on null
        $landing = \App\Models\LandingPage::with('slides')->first() ?? new \App\Models\LandingPage();
    @endphp

    <div id="preloader">
        <div class="trolley-wrap">
            <span class="trolley-run">🛒</span>
        </div>
        <p class="mt-4 text-[#2d6a4f] font-bold tracking-widest animate-pulse text-xs uppercase">Menyiapkan Pengalaman Belanja...</p>
    </div>

    <nav class="flex justify-between items-center px-6 lg:px-16 py-6 max-w-7xl mx-auto">
        <div class="flex items-center gap-3">
            {{-- Nama Toko Dinamis --}}
            @php 
                $namaToko = $landing->login_title ?? 'Sarana Agro Makmur';
                // Logic buat ambil inisial otomatis dari nama toko
                $inisial = collect(explode(' ', $namaToko))->map(function($word) {
                    return strtoupper(substr($word, 0, 1));
                })->take(2)->implode('');
            @endphp

            {{-- Logo/Inisial Dinamis --}}
            <img src="{{ $landing->login_logo_path ? asset('storage/' . $landing->login_logo_path) : asset('images/logotoko.png') }}" 
                class="w-10 h-10 object-contain"
                onerror="this.src='https://ui-avatars.com/api/?name={{ $inisial }}&background=2d6a4f&color=fff'">
            
            <span class="text-2xl font-bold text-[#2d6a4f]">{{ $namaToko }}</span>
        </div>
    <a href="{{ route('login') }}" class="px-8 py-3 bg-[#2d6a4f] text-white rounded-full font-bold hover:bg-[#1b4332] transition shadow-lg">Login</a>
    </nav>

    <header class="max-w-7xl mx-auto px-6 lg:px-16 flex flex-col lg:flex-row items-center py-10 lg:py-16 gap-12">
        <div class="lg:w-1/2 space-y-8 text-center lg:text-left">
            <h1 class="text-5xl lg:text-7xl font-black text-gray-900 leading-tight">
                {{ $landing->judul_h1 ?? 'Solusi Belanja' }} 
                <span class="text-[#2d6a4f]">{{ $landing->judul_highlight ?? 'Lengkap' }}</span> & Terpercaya.
            </h1>
            <p class="text-xl text-gray-600 max-w-xl mx-auto lg:mx-0 leading-relaxed text-justify lg:text-left">
                {{ $landing->deskripsi ?? 'Temukan segala kebutuhan Anda dalam satu tempat. Kami hadir dengan sistem manajemen modern untuk memberikan pelayanan terbaik bagi pelanggan setia Sarana Agro Makmur.' }}
            </p>
            <div class="pt-4">
                <a href="{{ route('login') }}" class="inline-block px-12 py-5 bg-[#2d6a4f] text-white rounded-2xl font-bold text-lg shadow-2xl shadow-green-900/40 hover:scale-105 transition-all tracking-widest">
                    MASUK KE SISTEM
                </a>
            </div>
        </div>

        <div class="lg:w-1/2 w-full">
            <div class="relative group">
                <div class="absolute -inset-4 bg-green-50 rounded-[4rem] -rotate-1 group-hover:rotate-0 transition-all duration-700"></div>
                
                <div class="swiper mySwiper shadow-2xl border-8 border-white">
                    <div class="swiper-wrapper">
                        {{-- Cek apakah ada slide di database --}}
                        @if($landing->slides && $landing->slides->count() > 0)
                            @foreach($landing->slides as $slide)
                                <div class="swiper-slide relative w-full h-full">
                                    @if($slide->type == 'video')
                                        <video autoplay muted loop playsinline class="absolute inset-0 w-full h-full object-cover">
                                            <source src="{{ asset('storage/' . $slide->path) }}" type="video/mp4">
                                        </video>
                                    @else
                                        <img src="{{ asset('storage/' . $slide->path) }}" class="absolute inset-0 w-full h-full object-cover">
                                    @endif
                                    <div class="absolute inset-0 bg-black/20"></div>
                                </div>
                            @endforeach
                        @else
                            {{-- Slide Default Jika Kosong --}}
                            <div class="swiper-slide relative w-full h-full bg-gray-800 flex items-center justify-center">
                                <img src="{{ asset('images/logotoko.png') }}" class="w-32 opacity-20" onerror="this.style.display='none'">
                                <p class="text-white font-bold opacity-50">Foto/video</p>
                            </div>
                        @endif
                    </div>
                    <div class="swiper-button-next"></div>
                    <div class="swiper-button-prev"></div>
                    <div class="swiper-pagination"></div>
                </div>
            </div>
        </div>
    </header>

    <footer class="bg-gray-50 border-t border-gray-100 py-12 mt-10 text-center">
        <p class="text-gray-400 text-sm font-medium italic">&copy; {{ date('Y') }} PT. Sarana Agro Makmur</p>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
    <script>
        var swiper = new Swiper(".mySwiper", {
            loop: true,
            autoplay: { delay: 5000, disableOnInteraction: false }, 
            pagination: { el: ".swiper-pagination", clickable: true },
            navigation: { nextEl: ".swiper-button-next", prevEl: ".swiper-button-prev" },
        });

        window.addEventListener('load', function() {
            setTimeout(() => {
                const preloader = document.getElementById('preloader');
                if(preloader) {
                    preloader.style.opacity = '0';
                    setTimeout(() => { preloader.style.display = 'none'; }, 500);
                }
            }, 1000);
        });
    </script>
</body>
</html>