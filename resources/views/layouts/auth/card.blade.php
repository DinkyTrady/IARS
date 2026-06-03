<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-indigo-50/50 antialiased dark:bg-zinc-950 flex items-center justify-center p-4 sm:p-8 relative overflow-hidden">
        <!-- Colorful Animated Mesh Background -->
        <div class="absolute inset-0 w-full h-full pointer-events-none">
            <div class="absolute -top-[20%] -left-[10%] w-[70vw] h-[70vw] max-w-[800px] max-h-[800px] rounded-full bg-blue-400/40 dark:bg-blue-600/40 blur-[100px] animate-[pulse_8s_ease-in-out_infinite]"></div>
            <div class="absolute top-[10%] -right-[20%] w-[60vw] h-[60vw] max-w-[700px] max-h-[700px] rounded-full bg-violet-400/40 dark:bg-violet-600/40 blur-[100px] animate-[pulse_10s_ease-in-out_infinite_reverse]"></div>
            <div class="absolute -bottom-[20%] left-[10%] w-[80vw] h-[80vw] max-w-[900px] max-h-[900px] rounded-full bg-pink-300/40 dark:bg-fuchsia-600/30 blur-[120px] animate-[pulse_12s_ease-in-out_infinite]"></div>
        </div>

        <div class="w-full max-w-[1000px] bg-white dark:bg-zinc-900 rounded-[2rem] shadow-2xl overflow-hidden flex flex-col md:flex-row border border-zinc-200 dark:border-zinc-800 z-10">
            <!-- Left Side: Image / Graphics -->
            <div class="relative hidden md:block md:w-1/2 bg-blue-950">
                <img src="https://images.unsplash.com/photo-1541339907198-e08756dedf3f?ixlib=rb-4.0.3&auto=format&fit=crop&w=1000&q=80" alt="IARS Background" class="absolute inset-0 w-full h-full object-cover opacity-50 mix-blend-luminosity">
                <div class="absolute inset-0 bg-gradient-to-br from-blue-600/80 via-violet-600/70 to-blue-950/90"></div>
                <div class="absolute inset-0 flex flex-col items-center justify-center p-12 text-center">
                    <div class="rounded-2xl bg-white/10 p-8 backdrop-blur-xl border border-white/20 shadow-2xl ring-1 ring-white/10 transform transition-all hover:scale-105 duration-300">
                        <blockquote class="space-y-4">
                            <p class="text-2xl font-semibold leading-relaxed tracking-wide text-white/95">&ldquo;Efisiensi ruang, optimalisasi waktu, wujudkan kampus yang cerdas.&rdquo;</p>
                            <footer class="text-base font-medium text-white/80">&mdash; IARS Team</footer>
                        </blockquote>
                    </div>
                </div>
            </div>

            <!-- Right Side: Form -->
            <div class="w-full md:w-1/2 p-8 sm:p-12 flex flex-col justify-center relative">
                <!-- Mobile Logo -->
                <a href="{{ route('home') }}" class="flex items-center gap-3 mb-8 md:hidden" wire:navigate>
                    <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-blue-600 to-violet-600 shadow-lg text-white">
                        <x-app-logo-icon class="size-6 fill-current text-white" />
                    </span>
                    <span class="font-bold text-2xl tracking-tight text-zinc-900 dark:text-white">IARS</span>
                </a>

                <!-- Desktop Logo -->
                <a href="{{ route('home') }}" class="hidden md:flex items-center gap-3 mb-8 hover:opacity-90 transition-opacity" wire:navigate>
                    <span class="flex h-12 w-12 items-center justify-center rounded-xl bg-gradient-to-br from-blue-600 to-violet-600 shadow-lg text-white">
                        <x-app-logo-icon class="size-7 fill-current text-white" />
                    </span>
                    <span class="font-extrabold text-3xl tracking-tight text-zinc-900 dark:text-white">IARS</span>
                </a>

                {{ $slot }}
            </div>
        </div>
        @fluxScripts
    </body>
</html>
