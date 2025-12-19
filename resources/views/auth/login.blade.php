<x-guest-layout>
    <div class="min-h-screen bg-gray-50 flex flex-col justify-center py-12 sm:px-6 lg:px-8 relative overflow-hidden">
        {{-- Background Decoration --}}
        <div class="absolute top-0 left-0 w-full h-full overflow-hidden pointer-events-none">
            <div class="absolute top-0 right-0 w-96 h-96 bg-[#4B3EE4]/10 rounded-full blur-3xl transform translate-x-1/2 -translate-y-1/2"></div>
            <div class="absolute bottom-0 left-0 w-96 h-96 bg-[#F0ECFF] rounded-full blur-3xl transform -translate-x-1/2 translate-y-1/2"></div>
        </div>

        <div class="sm:mx-auto sm:w-full sm:max-w-md relative z-10">
            <div class="flex justify-center mb-6">
                <div class="w-12 h-12 rounded-full bg-[#F0ECFF] flex items-center justify-center">
                   <span class="text-xl font-bold text-[#4B3EE4]">U</span>
                </div>
            </div>
            <h2 class="mt-2 text-center text-3xl font-bold tracking-tight text-gray-900">
                Selamat datang kembali
            </h2>
            <p class="mt-2 text-center text-sm text-gray-600">
                Masuk untuk mengelola keuanganmu
            </p>
        </div>

        <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md relative z-10">
            <div class="bg-white py-8 px-4 shadow-xl shadow-gray-200/50 sm:rounded-2xl sm:px-10 border border-gray-100">
                
                @if (session('status'))
                    <div class="mb-4 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-xl text-sm" role="alert">
                        {{ session('status') }}
                    </div>
                @endif
                
                @if ($errors->any())
                     <div class="mb-4 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl text-sm">
                        <ul class="list-disc pl-5 space-y-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form class="space-y-6" action="{{ route('login') }}" method="POST">
                    @csrf
                    
                    <div>
                        <label for="email" class="block text-sm font-semibold text-gray-700">Email</label>
                        <div class="mt-1">
                            <input id="email" name="email" type="email" autocomplete="email" required 
                                class="block w-full appearance-none rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-gray-900 placeholder-gray-400 focus:border-[#4B3EE4] focus:bg-white focus:outline-none focus:ring-1 focus:ring-[#4B3EE4] transition-all sm:text-sm"
                                value="{{ old('email') }}"
                                placeholder="nama@email.com">
                        </div>
                    </div>

                    <div>
                        <label for="password" class="block text-sm font-semibold text-gray-700">Password</label>
                        <div class="mt-1">
                            <input id="password" name="password" type="password" autocomplete="current-password" required 
                                class="block w-full appearance-none rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-gray-900 placeholder-gray-400 focus:border-[#4B3EE4] focus:bg-white focus:outline-none focus:ring-1 focus:ring-[#4B3EE4] transition-all sm:text-sm"
                                placeholder="••••••••">
                        </div>
                    </div>

                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <input id="remember-me" name="remember" type="checkbox" class="h-4 w-4 rounded border-gray-300 text-[#4B3EE4] focus:ring-[#4B3EE4]">
                            <label for="remember-me" class="ml-2 block text-sm text-gray-600">Ingat saya</label>
                        </div>
                    </div>

                    <div>
                        <button type="submit" class="flex w-full justify-center rounded-full bg-[#111827] px-4 py-3.5 text-sm font-bold text-white shadow-lg hover:bg-black hover:shadow-xl hover:-translate-y-0.5 transition-all duration-200 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#111827]">
                            Masuk
                        </button>
                    </div>
                </form>

            </div>
            
            <p class="mt-8 text-center text-xs text-gray-400">
                &copy; {{ date('Y') }} Uangku. Protected by AdminMiddleware.
            </p>
        </div>
    </div>
</x-guest-layout>
