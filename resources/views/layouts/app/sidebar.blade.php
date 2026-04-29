<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-white dark:bg-zinc-800">
        <flux:sidebar sticky collapsible="mobile" class="border-e border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
            <flux:sidebar.header>
                <x-app-logo :sidebar="true" href="{{ route('dashboard') }}" wire:navigate />
                <flux:sidebar.collapse class="lg:hidden" />
            </flux:sidebar.header>

            <flux:sidebar.nav>
                <flux:sidebar.group :heading="__('Menu Utama')" class="grid">
                    <flux:sidebar.item icon="home" :href="route('dashboard')" :current="request()->routeIs('dashboard')" wire:navigate>
                        {{ __('Dashboard') }}
                    </flux:sidebar.item>

                    <flux:sidebar.item icon="calendar-days" :href="route('reservations.index')" :current="request()->routeIs('reservations.index')" wire:navigate>
                        {{ __('Reservasi Saya') }}
                    </flux:sidebar.item>

                    <flux:sidebar.item icon="plus-circle" :href="route('reservations.create')" :current="request()->routeIs('reservations.create')" wire:navigate>
                        {{ __('Buat Reservasi') }}
                    </flux:sidebar.item>

                    <flux:sidebar.item icon="table-cells" :href="route('schedules.index')" :current="request()->routeIs('schedules.index')" wire:navigate>
                        {{ __('Jadwal Perkuliahan') }}
                    </flux:sidebar.item>
                </flux:sidebar.group>

                @can('admin')
                    <flux:sidebar.group :heading="__('Administrator')" class="grid">
                        <flux:sidebar.item icon="shield-check" :href="route('admin.reservations')" :current="request()->routeIs('admin.reservations')" wire:navigate>
                            {{ __('Persetujuan Reservasi') }}
                        </flux:sidebar.item>

                        <flux:sidebar.item icon="building-office-2" :href="route('admin.rooms')" :current="request()->routeIs('admin.rooms')" wire:navigate>
                            {{ __('Manajemen Ruangan') }}
                        </flux:sidebar.item>

                        <flux:sidebar.item icon="book-open" :href="route('admin.courses')" :current="request()->routeIs('admin.courses')" wire:navigate>
                            {{ __('Mata Kuliah') }}
                        </flux:sidebar.item>

                        <flux:sidebar.item icon="academic-cap" :href="route('admin.lecturers')" :current="request()->routeIs('admin.lecturers')" wire:navigate>
                            {{ __('Dosen') }}
                        </flux:sidebar.item>

                        <flux:sidebar.item icon="cpu-chip" :href="route('admin.schedules')" :current="request()->routeIs('admin.schedules')" wire:navigate>
                            {{ __('Optimasi Jadwal (GA)') }}
                        </flux:sidebar.item>

                        <flux:sidebar.item icon="users" :href="route('admin.users')" :current="request()->routeIs('admin.users')" wire:navigate>
                            {{ __('Manajemen Pengguna') }}
                        </flux:sidebar.item>
                    </flux:sidebar.group>
                @endcan
            </flux:sidebar.nav>

            <flux:spacer />

            <flux:sidebar.nav>
                <flux:sidebar.item icon="cog-6-tooth" :href="route('profile.edit')" :current="request()->routeIs('profile.edit')" wire:navigate>
                    {{ __('Pengaturan Akun') }}
                </flux:sidebar.item>
            </flux:sidebar.nav>

            <x-desktop-user-menu class="hidden lg:block" :name="auth()->user()->name" />
        </flux:sidebar>

        <!-- Mobile User Menu -->
        <flux:header class="lg:hidden">
            <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />

            <flux:spacer />

            <flux:dropdown position="top" align="end">
                <flux:profile
                    :initials="auth()->user()->initials()"
                    icon-trailing="chevron-down"
                />

                <flux:menu>
                    <flux:menu.radio.group>
                        <div class="p-0 text-sm font-normal">
                            <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                                <flux:avatar
                                    :name="auth()->user()->name"
                                    :initials="auth()->user()->initials()"
                                />

                                <div class="grid flex-1 text-start text-sm leading-tight">
                                    <flux:heading class="truncate">{{ auth()->user()->name }}</flux:heading>
                                    <flux:text class="truncate">{{ auth()->user()->email }}</flux:text>
                                </div>
                            </div>
                        </div>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <flux:menu.radio.group>
                        <flux:menu.item :href="route('profile.edit')" icon="cog" wire:navigate>
                            {{ __('Settings') }}
                        </flux:menu.item>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <form method="POST" action="{{ route('logout') }}" class="w-full">
                        @csrf
                        <flux:menu.item
                            as="button"
                            type="submit"
                            icon="arrow-right-start-on-rectangle"
                            class="w-full cursor-pointer"
                            data-test="logout-button"
                        >
                            {{ __('Log out') }}
                        </flux:menu.item>
                    </form>
                </flux:menu>
            </flux:dropdown>
        </flux:header>

        {{ $slot }}

        @fluxScripts
    </body>
</html>
