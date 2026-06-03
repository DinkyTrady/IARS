<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-white antialiased dark:bg-linear-to-b dark:from-neutral-950 dark:to-neutral-900">
        <div class="relative grid h-dvh flex-col items-center justify-center px-8 sm:px-0 lg:max-w-none lg:grid-cols-2 lg:px-0">
            <div class="bg-muted relative hidden h-full flex-col p-10 text-white lg:flex dark:border-e dark:border-neutral-800">
                <div class="absolute inset-0 bg-blue-950">
                    <img src="https://images.unsplash.com/photo-1541339907198-e08756dedf3f?ixlib=rb-4.0.3&auto=format&fit=crop&w=1920&q=80" alt="Campus Background" class="absolute inset-0 w-full h-full object-cover opacity-40 mix-blend-luminosity duration-1000 animate-in fade-in zoom-in-95">
                    <div class="absolute inset-0 bg-gradient-to-br from-blue-600/80 via-violet-600/70 to-blue-950/90"></div>
                </div>
                <a href="{{ route('home') }}" class="relative z-20 flex items-center text-xl font-semibold tracking-tight drop-shadow-md" wire:navigate>
                    <span class="flex h-12 w-12 items-center justify-center rounded-xl bg-white/10 backdrop-blur-md shadow-lg border border-white/20 me-3 transition-transform hover:scale-105">
                        <x-app-logo-icon class="h-7 w-7 fill-current text-white" />
                    </span>
                    {{ config('app.name', 'IARS') }}
                </a>

                @php
                    $quotes = [
                        ['Education is the most powerful weapon which you can use to change the world.', 'Nelson Mandela'],
                        ['The beautiful thing about learning is that no one can take it away from you.', 'B.B. King'],
                        ['Innovation distinguishes between a leader and a follower.', 'Steve Jobs'],
                        ['The only way to do great work is to love what you do.', 'Steve Jobs'],
                    ];
                    $randomQuote = $quotes[array_rand($quotes)];
                @endphp

                <div class="relative z-20 mt-auto rounded-2xl bg-white/10 p-8 backdrop-blur-xl border border-white/20 shadow-2xl ring-1 ring-white/10 transition-all duration-300 hover:bg-white/20">
                    <blockquote class="space-y-4">
                        <p class="text-2xl font-medium leading-relaxed tracking-wide text-white/95">&ldquo;{{ $randomQuote[0] }}&rdquo;</p>
                        <footer class="text-base font-medium text-white/80">&mdash; {{ $randomQuote[1] }}</footer>
                    </blockquote>
                </div>
            </div>
            <div class="w-full lg:p-8">
                <div class="mx-auto flex w-full flex-col justify-center space-y-6 sm:w-[350px]">
                    <a href="{{ route('home') }}" class="z-20 flex flex-col items-center gap-2 font-medium lg:hidden" wire:navigate>
                        <span class="flex h-9 w-9 items-center justify-center rounded-md">
                            <x-app-logo-icon class="size-9 fill-current text-black dark:text-white" />
                        </span>

                        <span class="sr-only">{{ config('app.name', 'Laravel') }}</span>
                    </a>
                    {{ $slot }}
                </div>
            </div>
        </div>
        @fluxScripts
    </body>
</html>
