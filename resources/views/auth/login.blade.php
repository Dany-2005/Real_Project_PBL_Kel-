<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - {{ config('app.name') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800;900&display=swap" rel="stylesheet">
    
    @php
        $landing = \App\Models\LandingPage::first();
        
        $titleColor = $landing->login_text_color ?? '#2d6a4f'; 
        $bgColor    = $landing->login_bg_color ?? '#2d6a4f';
        $fontFamily = $landing->login_font_family ?? "'Plus Jakarta Sans', sans-serif";
        $title      = $landing->login_title ?? 'Sarana Agro Makmur Sukses';
        $subtitle   = $landing->login_subtitle ?? 'SISTEM MANAJEMEN TOKO';

        $inisial = collect(explode(' ', $title))
            ->filter()
            ->map(function($word) {
                return strtoupper(substr(trim($word), 0, 1));
            })
            ->take(3)
            ->implode('');

        $cleanBgColor = str_replace('#', '', $bgColor);
    @endphp

    <style>
        * { font-family: {!! $fontFamily !!}; }
        body { overflow: hidden; background-color: #ffffff; }

        .panel-left {
            position: relative;
            width: 50%;
            height: 100vh;
            background-color: {{ $bgColor }};
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }

        .panel-left::after {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(to right,
                transparent 0%,
                transparent 65%,
                rgba(255,255,255,0.2) 80%,
                rgba(255,255,255,0.5) 90%,
                rgba(255,255,255,0.9) 97%,
                #ffffff 100%
            );
            pointer-events: none;
            z-index: 2;
        }

        .logo-card {
            position: relative;
            z-index: 3;
            background: rgba(255,255,255,0.13);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-radius: 2.5rem;
            box-shadow: 0 0 0 1px rgba(255,255,255,0.2), 0 32px 80px rgba(0,0,0,0.22);
            width: 22rem;
            height: 22rem;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 3rem;
        }

        .panel-right {
            flex: 1; 
            background: #ffffff;
            display: flex; 
            align-items: center; 
            justify-content: center;
            padding: 3rem;
            z-index: 10;
        }

        .input-field {
            background: #f8faf9;
            border: 2px solid #eef2f0;
            border-radius: 1rem;
            padding: 1rem 1.25rem;
            width: 100%;
            transition: all 0.2s;
        }
        .input-field:focus {
            border-color: #2d6a4f;
            background: #ffffff;
            outline: none;
            box-shadow: 0 0 0 4px #2d6a4f15;
        }

        .btn-submit {
            background: linear-gradient(135deg, #2d6a4f 0%, #1b4332 100%);
            color: white;
            padding: 1.1rem;
            border-radius: 1rem;
            width: 100%;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            box-shadow: 0 8px 20px #2d6a4f40;
            transition: all 0.3s;
            border: none;
            cursor: pointer;
        }
        .btn-submit:hover { 
            transform: translateY(-2px); 
            box-shadow: 0 12px 28px #2d6a4f60; 
        }

        .input-label { 
            font-size: 0.65rem; 
            font-weight: 800; 
            text-transform: uppercase; 
            color: #9ca3af; 
            letter-spacing: 0.1em; 
            margin-bottom: 0.4rem; 
            display: block; 
        }

        @keyframes fUp { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
        .anim { animation: fUp 0.6s ease both; }
    </style>
</head>
<body>

<div class="flex h-screen">
    {{-- KIRI: LOGO --}}
    <div class="panel-left hidden lg:flex">
        <div class="logo-card anim">
            <img src="{{ ($landing && $landing->login_logo_path) ? asset('storage/' . $landing->login_logo_path) : asset('images/logotoko.png') }}" 
                 class="w-full h-full object-contain drop-shadow-2xl"
                 onerror="this.src='https://ui-avatars.com/api/?name={{ urlencode($inisial) }}&background=ffffff&color={{ $cleanBgColor }}&size=256&length={{ strlen($inisial) }}'">
        </div>
    </div>

    {{-- KANAN: FORM --}}
    <div class="panel-right">
        <div class="w-full max-w-md">
            <div class="anim flex items-center gap-4 mb-2" style="animation-delay: 0.1s">
                {{-- Judul: warnanya dari login_text_color --}}
                <h1 class="text-3xl font-black tracking-tight" style="color: {{ $titleColor }}">
                    {{ $title }}
                </h1>
                <div class="p-2 rounded-xl bg-green-50">
                    <span class="text-sm font-black text-[#2d6a4f]">{{ $inisial }}</span>
                </div>
            </div>

            <p class="anim text-[10px] font-bold text-gray-400 uppercase tracking-[0.2em] mb-8" style="animation-delay: 0.2s">
                {{ $subtitle }}
            </p>

            <h2 class="anim text-2xl font-black text-gray-900 mb-8" style="animation-delay: 0.3s">
                LOGIN 
            </h2>

            <form method="POST" action="{{ route('login') }}" class="space-y-6">
                @csrf
                <div class="anim" style="animation-delay: 0.4s">
                    <label class="input-label">Email Address</label>
                    <input type="email" name="email" required class="input-field" placeholder="name@gmail.com" value="{{ old('email') }}">
                    @error('email')
                        <span class="text-red-500 text-[10px] font-bold mt-1 uppercase">{{ $message }}</span>
                    @enderror
                </div>

                <div class="anim" style="animation-delay: 0.5s" x-data="{ show: false }">
                    <label class="input-label">Password</label>
                    <div class="relative">
                        <input :type="show ? 'text' : 'password'" name="password" required class="input-field" placeholder="••••••••">
                        <button type="button" @click="show = !show" class="absolute right-4 top-4 text-gray-400 hover:text-gray-600 transition-colors">
                             <svg x-show="!show" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                             <svg x-show="show" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"/></svg>
                        </button>
                    </div>
                </div>

                <div class="anim flex justify-between items-center" style="animation-delay: 0.6s">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="remember" class="w-4 h-4 rounded" style="accent-color: #2d6a4f">
                        <span class="text-[10px] font-bold text-gray-400 uppercase">Ingat Saya</span>
                    </label>
                    <a href="{{ route('password.request') }}" class="text-[10px] font-bold uppercase hover:underline text-[#2d6a4f]">Lupa Password?</a>
                </div>

                <div class="anim pt-2" style="animation-delay: 0.7s">
                    <button type="submit" class="btn-submit">Login</button>
                </div>

                <div class="anim mt-8" style="animation-delay: 0.8s">
                    <a href="/" class="input-label mb-0 hover:opacity-70 transition-opacity">
                        ← Kembali ke Beranda
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

</body>
</html>