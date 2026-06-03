<x-layouts::auth :title="__('Register')">
    <div class="flex flex-col gap-8 w-full max-w-md mx-auto">
        <div class="text-left space-y-2 mb-2">
            <h1 class="text-3xl font-bold tracking-tight text-zinc-900 dark:text-white">Buat Akun Baru</h1>
            <p class="text-zinc-500 dark:text-zinc-400 font-medium text-sm">Silakan lengkapi data diri Anda untuk mendaftar</p>
        </div>

        <!-- Session Status -->
        <x-auth-session-status class="text-center" :status="session('status')" />

        <form method="POST" action="{{ route('register.store') }}" class="flex flex-col gap-5">
            @csrf
            
            <!-- Name -->
            <div class="space-y-1.5">
                <label for="name" class="block text-sm font-semibold text-zinc-700 dark:text-zinc-300">Nama Lengkap</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-zinc-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                    </div>
                    <input id="name" type="text" name="name" value="{{ old('name') }}" required autofocus autocomplete="name" placeholder="John Doe"
                        class="block w-full pl-10 pr-3 py-2.5 sm:text-sm border border-zinc-200 dark:border-zinc-700 rounded-xl text-zinc-900 dark:text-white bg-white dark:bg-zinc-900 focus:ring-2 focus:ring-blue-600 focus:border-blue-600 dark:focus:ring-blue-500 transition-all duration-200 shadow-sm"
                    >
                </div>
                @error('name')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Email Address -->
            <div class="space-y-1.5">
                <label for="email" class="block text-sm font-semibold text-zinc-700 dark:text-zinc-300">Email Address</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-zinc-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="20" height="16" x="2" y="4" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg>
                    </div>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" required autocomplete="email" placeholder="name@example.com"
                        class="block w-full pl-10 pr-3 py-2.5 sm:text-sm border border-zinc-200 dark:border-zinc-700 rounded-xl text-zinc-900 dark:text-white bg-white dark:bg-zinc-900 focus:ring-2 focus:ring-blue-600 focus:border-blue-600 dark:focus:ring-blue-500 transition-all duration-200 shadow-sm"
                    >
                </div>
                @error('email')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Password -->
            <div class="space-y-1.5">
                <label for="password" class="block text-sm font-semibold text-zinc-700 dark:text-zinc-300">Password</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-zinc-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="11" x="3" y="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                    </div>
                    <input id="password" type="password" name="password" required autocomplete="new-password" placeholder="••••••••"
                        class="block w-full pl-10 pr-3 py-2.5 sm:text-sm border border-zinc-200 dark:border-zinc-700 rounded-xl text-zinc-900 dark:text-white bg-white dark:bg-zinc-900 focus:ring-2 focus:ring-blue-600 focus:border-blue-600 dark:focus:ring-blue-500 transition-all duration-200 shadow-sm"
                    >
                </div>
                @error('password')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Confirm Password -->
            <div class="space-y-1.5">
                <label for="password_confirmation" class="block text-sm font-semibold text-zinc-700 dark:text-zinc-300">Konfirmasi Password</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-zinc-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="11" x="3" y="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                    </div>
                    <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password" placeholder="••••••••"
                        class="block w-full pl-10 pr-3 py-2.5 sm:text-sm border border-zinc-200 dark:border-zinc-700 rounded-xl text-zinc-900 dark:text-white bg-white dark:bg-zinc-900 focus:ring-2 focus:ring-blue-600 focus:border-blue-600 dark:focus:ring-blue-500 transition-all duration-200 shadow-sm"
                    >
                </div>
            </div>

            <!-- Submit Button -->
            <button type="submit" class="mt-2 w-full flex justify-center py-2.5 px-4 border border-transparent rounded-xl shadow-sm text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-600 transition-all duration-200 active:scale-[0.98]">
                Buat Akun
            </button>
        </form>

        <div class="text-center text-sm text-zinc-600 dark:text-zinc-400 mt-2">
            Sudah punya akun?
            <a href="{{ route('login') }}" wire:navigate class="font-semibold text-blue-600 hover:text-blue-700 dark:text-blue-500 transition-colors">Masuk di sini</a>
        </div>
    </div>
</x-layouts::auth>
