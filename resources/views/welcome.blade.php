<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Uangku</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />

        <!-- Styles / Scripts -->
        @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @else
            {{-- fallback: biarkan kosong, asumsi kamu akan pakai Vite di local --}}
        @endif

        <style>
            /* Simple modal behaviour tanpa JS: buka saat hash = #signup-modal */
            #signup-modal {
                opacity: 0;
                pointer-events: none;
                transition: opacity 0.2s ease-out;
            }

            #signup-modal:target {
                opacity: 1;
                pointer-events: auto;
            }
        </style>
    </head>
    <body class="bg-white text-gray-900 min-h-screen font-[Instrument_Sans]">
        
        {{-- Navigation --}}
        <header class="fixed top-0 w-full z-50 bg-white/80 backdrop-blur-md border-b border-gray-100">
            <div class="max-w-6xl mx-auto px-6 lg:px-8 h-16 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-full bg-[#F0ECFF] flex items-center justify-center">
                        <span class="text-xs font-bold text-[#4B3EE4]">U</span>
                    </div>
                    <span class="font-bold tracking-tight text-lg text-gray-900">uangku</span>
                </div>

                <nav class="hidden md:flex items-center gap-8 text-[14px] text-gray-700 font-semibold">
                    <a href="#features" class="hover:text-[#4B3EE4] transition-colors">Fitur</a>
                    <a href="#how-it-works" class="hover:text-[#4B3EE4] transition-colors">Cara Kerja</a>
                    <a href="#pricing" class="hover:text-[#4B3EE4] transition-colors">Harga</a>
                    <a href="#faq" class="hover:text-[#4B3EE4] transition-colors">FAQ</a>
                    @if (Route::has('login'))
                        @auth
                            <a
                                href="{{ url('/dashboard') }}"
                                class="px-4 py-2 rounded-full border border-gray-200 text-gray-900 hover:border-[#4B3EE4] hover:text-[#4B3EE4] transition-colors"
                            >
                                Dashboard
                            </a>
                        @else
                            <!-- Login hidden for internal use
                            <a
                                href="{{ route('login') }}"
                                class="px-4 py-2 rounded-full text-gray-600 hover:text-[#4B3EE4] transition-colors"
                            >
                                Masuk
                            </a>
                            -->
                            @if (Route::has('register'))
                                <a href="#signup-modal" class="px-5 py-2 rounded-full bg-[#4B3EE4] text-white text-sm font-semibold shadow-lg shadow-[#4B3EE4]/30 hover:bg-[#3b31b7] transition-all transform hover:-translate-y-0.5">
                                    Daftar
                                </a>
                            @endif
                        @endauth
                    @endif
                </nav>
            </div>
        </header>

        {{-- Hero --}}
        <section class="pt-32 pb-20 lg:pt-40 lg:pb-32 overflow-hidden">
            <div class="max-w-6xl mx-auto px-6 lg:px-8">
                <main class="flex flex-col lg:flex-row items-center gap-12 lg:gap-20">
                    <div class="flex-1 max-w-xl">
                        <p class="inline-flex items-center gap-2 text-[11px] uppercase tracking-widest text-[#4B3EE4] font-bold mb-6 bg-[#F0ECFF] px-3 py-1 rounded-full">
                            <span class="w-2 h-2 rounded-full bg-[#4B3EE4] animate-pulse"></span>
                            Asisten keuangan pribadi
                        </p>

                        <h1 class="text-5xl lg:text-6xl font-bold tracking-tight mb-6 text-gray-900 leading-[1.1]">
                            Urus uang harian<br class="hidden lg:block"> cukup lewat chat.
                        </h1>

                        <p class="text-lg leading-relaxed text-gray-600 mb-8 max-w-lg">
                            Uangku membantu kamu <span class="font-semibold text-gray-900">merekam pengeluaran, mengingatkan tagihan, dan menyiapkan tabungan</span> langsung dari Telegram.
                        </p>

                        <div class="flex flex-col sm:flex-row items-start sm:items-center gap-5 mb-10">
                            <a
                                href="#signup-modal"
                                class="inline-flex items-center justify-center px-8 py-3.5 rounded-full bg-[#4B3EE4] text-white text-base font-semibold shadow-xl shadow-[#4B3EE4]/25 hover:bg-[#3b31b7] hover:shadow-2xl hover:shadow-[#4B3EE4]/40 transition-all transform hover:-translate-y-1"
                            >
                                Coba gratis 7 hari
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 ml-2" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-4.293-4.293a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </a>

                            <div class="flex items-center gap-3 text-sm text-gray-500">
                                <span class="flex -space-x-2">
                                     <img class="w-8 h-8 rounded-full border-2 border-white bg-gray-100" src="https://ui-avatars.com/api/?name=A&background=random" alt="User">
                                     <img class="w-8 h-8 rounded-full border-2 border-white bg-gray-100" src="https://ui-avatars.com/api/?name=B&background=random" alt="User">
                                     <img class="w-8 h-8 rounded-full border-2 border-white bg-gray-100" src="https://ui-avatars.com/api/?name=C&background=random" alt="User">
                                </span>
                                <p>100+ pengguna early access</p>
                            </div>
                        </div>

                        <p class="text-xs text-gray-400 font-medium tracking-wide uppercase mb-2">Didukung oleh</p>
                        <div class="flex items-center gap-5 opacity-60 grayscale hover:grayscale-0 transition-all duration-300 cursor-default">
                            <div class="h-8 flex items-center gap-3 group">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 48 48" class="h-9 w-9 text-[#2CA5E0] fill-current">
                                    <path d="M24 4C12.95 4 4 12.95 4 24s8.95 20 20 20 20-8.95 20-20S35.05 4 24 4zm10.2 15.2l-3.5 16.5c-.2.9-1.2 1.3-1.9.8l-8.6-6-4.8 4.6c-.5.5-1.3.4-1.5-.3l-2.2-7.8-6.4-2c-.9-.3-.9-1.6.2-2l24.9-9.6c.9-.3 1.8.5 1.5 1.5z"/>
                                </svg>
                                <span class="text-xl font-bold text-[#2CA5E0] mt-0.5">Telegram</span>
                            </div>
                        </div>
                    </div>

                    {{-- Hero phones --}}
                    <div class="flex-1 w-full flex justify-center lg:justify-end relative">
                        <div class="absolute inset-0 bg-gradient-to-tr from-[#F0ECFF] via-[#E0FDF4] to-transparent rounded-full blur-3xl opacity-50 -z-10 transform translate-x-10 translate-y-10"></div>
                        <img
                            src="{{ asset('mockup.png') }}"
                            alt="Mockup aplikasi Uangku"
                            class="relative w-full max-w-sm h-auto drop-shadow-2xl rounded-[2.5rem] transform hover:scale-[1.02] transition-transform duration-500 ease-out"
                            loading="lazy"
                        >
                    </div>
                </main>
            </div>
        </section>

        {{-- Features --}}
        <section id="features" class="py-24 bg-gray-50">
            <div class="max-w-6xl mx-auto px-6 lg:px-8">
                <div class="max-w-2xl text-center mx-auto mb-16">
                    <h2 class="text-3xl font-bold text-gray-900 mb-4">Fitur yang kamu butuhkan</h2>
                    <p class="text-lg text-gray-600">
                        Dirancang simpel untuk kamu yang sibuk. Tanpa grafik ribet, cukup chat.
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    <div class="bg-white p-8 rounded-2xl shadow-sm border border-gray-100 hover:shadow-md hover:border-[#4B3EE4]/20 transition-all group">
                        <div class="w-12 h-12 rounded-xl bg-[#F0ECFF] text-[#4B3EE4] flex items-center justify-center mb-6 group-hover:scale-110 transition-transform">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" /></svg>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 mb-3">Catat via Chat</h3>
                        <p class="text-gray-600 leading-relaxed">Cukup kirim pesan seperti “kopi 25k” di Telegram, transaksi langsung tersimpan rapi.</p>
                    </div>
                    <div class="bg-white p-8 rounded-2xl shadow-sm border border-gray-100 hover:shadow-md hover:border-[#4B3EE4]/20 transition-all group">
                        <div class="w-12 h-12 bg-[#E0F2FE] text-[#0284C7] rounded-xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform">
                             <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 mb-3">Pengingat Pintar</h3>
                        <p class="text-gray-600 leading-relaxed">Notifikasi halus sebelum jatuh tempo tagihan atau utang, supaya kamu tenang.</p>
                    </div>
                    <div class="bg-white p-8 rounded-2xl shadow-sm border border-gray-100 hover:shadow-md hover:border-[#4B3EE4]/20 transition-all group">
                        <div class="w-12 h-12 bg-[#DCFCE7] text-[#16A34A] rounded-xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform">
                             <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" /></svg>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 mb-3">Insight Harian</h3>
                        <p class="text-gray-600 leading-relaxed">Ringkasan pengeluaran dan kategori terbesar dalam bahasa manusia, bukan grafik rumit.</p>
                    </div>
                </div>
            </div>
        </section>

        {{-- How It Works --}}
        <section id="how-it-works" class="py-24 bg-white relative overflow-hidden">
            <div class="max-w-6xl mx-auto px-6 lg:px-8 relative z-10">
                <div class="flex flex-col lg:flex-row items-center gap-16">
                    <div class="flex-1 space-y-8">
                        <h2 class="text-3xl font-bold text-gray-900">Cara kerja Uangku</h2>
                        <div class="space-y-6">
                            <div class="flex gap-4">
                                <div class="flex-shrink-0 w-8 h-8 rounded-full bg-[#111827] text-white flex items-center justify-center font-bold text-sm">1</div>
                                <div>
                                    <h4 class="text-lg font-bold text-gray-900">Daftar Akun</h4>
                                    <p class="text-gray-600 mt-1">Buat akun lewat tombol "Coba gratis" dalam hitungan detik.</p>
                                </div>
                            </div>
                            <div class="flex gap-4">
                                <div class="flex-shrink-0 w-8 h-8 rounded-full bg-[#111827] text-white flex items-center justify-center font-bold text-sm">2</div>
                                <div>
                                    <h4 class="text-lg font-bold text-gray-900">Sambungkan Telegram</h4>
                                    <p class="text-gray-600 mt-1">Klik link yang tersedia untuk terhubung dengan bot Uangku.</p>
                                </div>
                            </div>
                            <div class="flex gap-4">
                                <div class="flex-shrink-0 w-8 h-8 rounded-full bg-[#111827] text-white flex items-center justify-center font-bold text-sm">3</div>
                                <div>
                                    <h4 class="text-lg font-bold text-gray-900">Mulai Chatting</h4>
                                    <p class="text-gray-600 mt-1">Kirim transaksi kamu, dan biarkan kami yang mencatatnya.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="flex-1 bg-gray-50 rounded-3xl p-8 border border-gray-100 transform rotate-2 hover:rotate-0 transition-transform duration-500">
                        <!-- Chat simulation -->
                        <div class="space-y-4 font-mono text-xs sm:text-sm">
                            <div class="flex justify-end">
                                <div class="bg-[#E0F2FE] text-[#0284C7] px-4 py-2 rounded-2xl rounded-tr-none max-w-[80%] shadow-sm">
                                    Makan siang 35k
                                </div>
                            </div>
                            <div class="flex justify-start">
                                <div class="bg-white text-gray-700 px-4 py-2 rounded-2xl rounded-tl-none border border-gray-200 shadow-sm">
                                    ✅ Tercatat! <strong>Makan siang</strong> (F&B) sebesar Rp35.000.<br>
                                    Total pengeluaran hari ini: Rp85.000.
                                </div>
                            </div>
                            <div class="flex justify-end mt-6">
                                <div class="bg-[#E0F2FE] text-[#0284C7] px-4 py-2 rounded-2xl rounded-tr-none max-w-[80%] shadow-sm">
                                    Info budget
                                </div>
                            </div>
                            <div class="flex justify-start">
                                <div class="bg-white text-gray-700 px-4 py-2 rounded-2xl rounded-tl-none border border-gray-200 shadow-sm">
                                    📊 Budget F&B kamu sisa 40% bulan ini.<br>
                                    Saran: Masak sendiri besok? 😄
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- Pricing --}}
        <section id="pricing" class="py-24 bg-gray-50">
            <div class="max-w-7xl mx-auto px-6 lg:px-8">
                <div class="text-center mb-16">
                     <h2 class="text-3xl font-bold text-gray-900 mb-4">Investasi untuk Ketenanganmu</h2>
                     <p class="text-lg text-gray-600">Pilih paket yang pas. Bisa ganti kapan aja.</p>
                </div>
               
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8 items-start">
                    
                    {{-- Tier 1: Trial --}}
                    <div class="bg-white rounded-3xl p-8 shadow-sm border border-gray-100 hover:shadow-md transition-all relative">
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Starter</h3>
                        <div class="flex items-baseline gap-1 mb-6">
                            <span class="text-4xl font-bold text-gray-900">Rp0</span>
                            <span class="text-gray-500">/ 7 hari</span>
                        </div>
                        <p class="text-sm text-gray-600 mb-8 leading-relaxed">
                            Akses penuh semua fitur untuk kamu yang baru mau coba kemudahan Uangku.
                        </p>
                        <ul class="space-y-4 mb-8 text-sm text-gray-600">
                            <li class="flex items-center gap-3">
                                <svg class="w-5 h-5 text-[#4B3EE4]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                <span>Akses Full via Telegram</span>
                            </li>
                            <li class="flex items-center gap-3">
                                <svg class="w-5 h-5 text-[#4B3EE4]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                <span>Catat Transaksi Unlimited</span>
                            </li>
                            <li class="flex items-center gap-3">
                                <svg class="w-5 h-5 text-[#4B3EE4]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                <span>Laporan Harian</span>
                            </li>
                        </ul>
                        <a href="#signup-modal" class="block w-full py-3 px-6 text-center rounded-xl border border-gray-200 text-gray-900 font-semibold hover:border-[#4B3EE4] hover:text-[#4B3EE4] transition-colors">
                            Coba Gratis
                        </a>
                    </div>

                    {{-- Tier 2: Standard (15k) --}}
                    <div class="bg-white rounded-3xl p-8 shadow-xl border border-[#4B3EE4]/20 relative transform md:-translate-y-4 z-10">
                        <div class="absolute top-0 right-0 left-0 -mt-4 flex justify-center">
                            <span class="bg-[#4B3EE4] text-white text-xs font-bold px-3 py-1 rounded-full uppercase tracking-widest shadow-lg">Paling Hemat</span>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Personal</h3>
                        <div class="flex items-baseline gap-1 mb-6">
                            <span class="text-4xl font-bold text-gray-900">15rb</span>
                            <span class="text-gray-500">/ bulan</span>
                        </div>
                        <p class="text-sm text-gray-600 mb-8 leading-relaxed">
                             Solusi lengkap untuk mengatur keuangan pribadi sehari-hari tanpa ribet.
                        </p>
                        <ul class="space-y-4 mb-8 text-sm text-gray-600">
                             <li class="flex items-center gap-3">
                                <svg class="w-5 h-5 text-[#4B3EE4]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                <span><b>Semua fitur Starter</b></span>
                            </li>
                            <li class="flex items-center gap-3">
                                <svg class="w-5 h-5 text-[#4B3EE4]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                <span>Limit Kategori Unlimited</span>
                            </li>
                            <li class="flex items-center gap-3">
                                <svg class="w-5 h-5 text-[#4B3EE4]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                <span>Export Data ke Excel</span>
                            </li>
                             <li class="flex items-center gap-3">
                                <svg class="w-5 h-5 text-[#4B3EE4]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                <span>Support Prioritas</span>
                            </li>
                        </ul>
                        <a href="#signup-modal" class="block w-full py-3.5 px-6 text-center rounded-xl bg-[#111827] text-white font-semibold hover:bg-black hover:shadow-lg transition-all transform hover:-translate-y-0.5">
                            Pilih Personal
                        </a>
                    </div>

                    {{-- Tier 3: Premium (30k) --}}
                    <div class="bg-white rounded-3xl p-8 shadow-sm border border-gray-100 hover:shadow-md transition-all">
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Pro</h3>
                        <div class="flex items-baseline gap-1 mb-6">
                            <span class="text-4xl font-bold text-gray-900">30rb</span>
                            <span class="text-gray-500">/ bulan</span>
                        </div>
                        <p class="text-sm text-gray-600 mb-8 leading-relaxed">
                            Fitur canggih dengan AI untuk analisa kebiasaan belanja kamu.
                        </p>
                        <ul class="space-y-4 mb-8 text-sm text-gray-600">
                            <li class="flex items-center gap-3">
                                <svg class="w-5 h-5 text-[#4B3EE4]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                <span><b>Semua fitur Personal</b></span>
                            </li>
                             <li class="flex items-center gap-3">
                                <svg class="w-5 h-5 text-[#4B3EE4]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                <span>Scan Struk Belanja (OCR)</span>
                            </li>
                            <li class="flex items-center gap-3">
                                <svg class="w-5 h-5 text-[#4B3EE4]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                <span>Insight &amp; Saran AI</span>
                            </li>
                        </ul>
                        <a href="#signup-modal" class="block w-full py-3 px-6 text-center rounded-xl border border-gray-200 text-gray-900 font-semibold hover:border-[#4B3EE4] hover:text-[#4B3EE4] transition-colors">
                            Pilih Pro
                        </a>
                    </div>

                </div>
            </div>
        </section>

        {{-- FAQ --}}
        <section id="faq" class="py-24 bg-white">
            <div class="max-w-3xl mx-auto px-6 lg:px-8">
                <h2 class="text-3xl font-bold text-gray-900 mb-10 text-center">Tanya Jawab</h2>
                <div class="space-y-4">
                    <details class="group border border-gray-200 rounded-2xl p-6 open:bg-gray-50 transition-colors">
                        <summary class="cursor-pointer font-bold text-gray-900 flex justify-between items-center list-none">
                            Apakah data saya aman?
                            <span class="transform group-open:rotate-180 transition-transform">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                            </span>
                        </summary>
                        <p class="mt-4 text-gray-600 leading-relaxed">
                            Data transaksi kamu disimpan dengan aman. Kami tidak menjual data ke pihak ketiga.
                        </p>
                    </details>
                    <details class="group border border-gray-200 rounded-2xl p-6 open:bg-gray-50 transition-colors">
                         <summary class="cursor-pointer font-bold text-gray-900 flex justify-between items-center list-none">
                            Apa yang terjadi setelah masa coba habis?
                            <span class="transform group-open:rotate-180 transition-transform">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                            </span>
                        </summary>
                        <p class="mt-4 text-gray-600 leading-relaxed">
                            Akun kamu tetap ada, tapi fitur chat bot akan dibatasi. Kamu bisa upgrade kapan saja untuk melanjutkan.
                        </p>
                    </details>
                    <details class="group border border-gray-200 rounded-2xl p-6 open:bg-gray-50 transition-colors">
                         <summary class="cursor-pointer font-bold text-gray-900 flex justify-between items-center list-none">
                            Bisa dipakai bareng pasangan?
                            <span class="transform group-open:rotate-180 transition-transform">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                            </span>
                        </summary>
                        <p class="mt-4 text-gray-600 leading-relaxed">
                            Fitur "Shared Wallet" sedang kami kembangkan dan akan segera hadir!
                        </p>
                    </details>
                </div>
            </div>
        </section>

        {{-- Footer --}}
        <footer class="py-12 bg-white border-t border-gray-100">
             <div class="max-w-6xl mx-auto px-6 lg:px-8 flex flex-col md:flex-row justify-between items-center gap-6">
                 <p class="text-sm text-gray-500">© {{ date('Y') }} Uangku. All rights reserved.</p>
                 <div class="flex gap-6 text-sm font-medium text-gray-600">
                     <a href="#" class="hover:text-[#4B3EE4]">Privacy</a>
                     <a href="#" class="hover:text-[#4B3EE4]">Terms</a>
                     <a href="mailto:hello@uangku.id" class="hover:text-[#4B3EE4]">Contact</a>
                 </div>
             </div>
        </footer>

        {{-- Modal registrasi pakai anchor #signup-modal (tanpa JS) --}}
        @if (Route::has('register'))
            <div
                id="signup-modal"
                class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center px-4 z-[60]"
            >
                <a href="#" class="absolute inset-0 cursor-default"></a>
                <div class="relative bg-white rounded-2xl shadow-2xl max-w-lg w-full p-8 space-y-6 transform transition-all scale-100">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="text-xs font-bold text-[#4B3EE4] mb-1 uppercase tracking-widest">Coba gratis 7 hari</p>
                            <h2 class="text-2xl font-bold text-gray-900">Buat akun Uangku</h2>
                            <p class="text-sm text-gray-500 mt-2">Isi data di bawah, lalu sambungkan Telegram untuk mulai mencatat transaksi.</p>
                        </div>
                        <a href="#" class="text-gray-400 hover:text-gray-900 transition-colors">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                        </a>
                    </div>

                    <form
                        action="{{ route('register') }}"
                        method="post"
                        class="grid grid-cols-1 sm:grid-cols-2 gap-5"
                    >
                        @csrf
                        <input type="hidden" name="preferred_channel" value="telegram">
                        <div class="sm:col-span-1 space-y-1">
                            <label for="modal_name" class="block text-sm font-semibold text-gray-700">Nama lengkap</label>
                            <input
                                id="modal_name"
                                name="name"
                                type="text"
                                autocomplete="name"
                                class="w-full px-4 py-3 rounded-xl border border-gray-200 bg-gray-50 text-gray-900 placeholder:text-gray-400 focus:outline-none focus:border-[#4B3EE4] focus:ring-1 focus:ring-[#4B3EE4] transition-all"
                                placeholder="Contoh: Rivaldi"
                                required
                            >
                        </div>
                        <div class="sm:col-span-1 space-y-1">
                            <label for="modal_email" class="block text-sm font-semibold text-gray-700">Email</label>
                            <input
                                id="modal_email"
                                name="email"
                                type="email"
                                autocomplete="email"
                                class="w-full px-4 py-3 rounded-xl border border-gray-200 bg-gray-50 text-gray-900 placeholder:text-gray-400 focus:outline-none focus:border-[#4B3EE4] focus:ring-1 focus:ring-[#4B3EE4] transition-all"
                                placeholder="nama@email.com"
                                required
                            >
                        </div>
                        <div class="sm:col-span-1 space-y-1">
                            <label for="modal_password" class="block text-sm font-semibold text-gray-700">Password</label>
                            <input
                                id="modal_password"
                                name="password"
                                type="password"
                                autocomplete="new-password"
                                class="w-full px-4 py-3 rounded-xl border border-gray-200 bg-gray-50 text-gray-900 placeholder:text-gray-400 focus:outline-none focus:border-[#4B3EE4] focus:ring-1 focus:ring-[#4B3EE4] transition-all"
                                placeholder="Min. 8 karakter"
                                required
                            >
                        </div>
                        <div class="sm:col-span-1 space-y-1">
                            <label for="modal_password_confirmation" class="block text-sm font-semibold text-gray-700">Ulangi password</label>
                            <input
                                id="modal_password_confirmation"
                                name="password_confirmation"
                                type="password"
                                autocomplete="new-password"
                                class="w-full px-4 py-3 rounded-xl border border-gray-200 bg-gray-50 text-gray-900 placeholder:text-gray-400 focus:outline-none focus:border-[#4B3EE4] focus:ring-1 focus:ring-[#4B3EE4] transition-all"
                                placeholder="Ketik ulang"
                                required
                            >
                        </div>
                        <div class="sm:col-span-2 pt-2">
                            <button
                                type="submit"
                                class="flex items-center justify-center px-6 py-3.5 rounded-full bg-[#111827] text-white text-sm font-bold hover:bg-black transition-all w-full shadow-lg hover:shadow-xl"
                            >
                                Buat akun &amp; mulai coba gratis
                            </button>
                            <p class="text-xs text-center text-gray-500 mt-4">
                                Dengan mendaftar kamu menyetujui <a href="#" class="underline hover:text-gray-900">Syarat & Ketentuan</a> kami.
                            </p>
                        </div>
                    </form>
                </div>
            </div>
        @endif
    </body>
</html>
