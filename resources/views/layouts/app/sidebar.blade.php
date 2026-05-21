<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-white">
        <flux:sidebar sticky collapsible class="{{ auth()->user()->role === 'admin' ? 'sidebar-admin bg-zinc-950 border-e border-zinc-950 text-zinc-100' : 'sidebar-user bg-white border-e border-zinc-200 text-zinc-800' }}">
            <flux:sidebar.header>
                <x-app-logo :sidebar="true" href="{{ route('dashboard') }}" wire:navigate />
                <flux:sidebar.toggle class="lg:hidden" icon="x-mark" />
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

                    <flux:sidebar.item icon="calendar" :href="route('reservations.schedule')" :current="request()->routeIs('reservations.schedule')" wire:navigate>
                        {{ __('Jadwal Reservasi') }}
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

        <style>
    /* ==========================================
       BODY BACKGROUND PERAN-SPESIFIK (UX HIGH-END)
       ========================================== */
    body:has(.sidebar-user) {
        background-color: #f8fafc !important; /* Soft Slate-50 untuk panel siswa/dosen agar kartu terlihat melayang */
    }
    
    body:has(.sidebar-admin) {
        background-color: #f1f5f9 !important; /* Slate-100 untuk panel administratif agar tabel & statistik menonjol */
    }

    /* ==========================================
       THEME 1: SIDEBAR USER (MAHASISWA/DOSEN - BERSIH & BIRU)
       ========================================== */
    /* Sidebar Item Default */
    .sidebar-user [data-flux-sidebar-item], 
    .sidebar-user flux\:sidebar\.item {
        color: #4b5563 !important; /* gray-600 */
        margin-bottom: 2px !important;
        transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1) !important;
        display: flex;
        font-weight: 500 !important;
        border-radius: 8px !important;
    }
    
    /* Hover State */
    .sidebar-user [data-flux-sidebar-item]:hover,
    .sidebar-user flux\:sidebar\.item:hover {
        background-color: #eff6ff !important; /* blue-50 */
        color: #1d4ed8 !important; /* blue-700 */
        transform: translateX(4px);
    }
    
    /* Active State (Selected Menu) */
    .sidebar-user [data-flux-sidebar-item][current],
    .sidebar-user flux\:sidebar\.item[current] {
        background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%) !important; /* Ocean Blue Gradient */
        color: #ffffff !important;
        box-shadow: 0 4px 12px rgba(37, 99, 235, 0.15) !important;
    }

    /* Group Headings */
    .sidebar-user [data-flux-sidebar-group] h2, 
    .sidebar-user flux\:sidebar\.group h2 {
        color: #64748b !important; /* slate-500 */
        font-weight: 700 !important;
        text-transform: uppercase !important;
        letter-spacing: 0.075em !important;
        font-size: 0.65rem !important;
        margin-top: 1.5rem !important;
        padding-left: 0.5rem;
    }

    /* Profile Section Divider */
    .sidebar-user [data-test="sidebar-menu-button"] {
        border-top: 1px solid #f3f4f6 !important;
        margin-top: 8px !important;
        padding-top: 12px !important;
        transition: all 0.2s ease !important;
    }

    /* Profile Hover */
    .sidebar-user [data-flux-profile]:hover,
    .sidebar-user button[data-test="sidebar-menu-button"]:hover,
    .sidebar-user [data-flux-sidebar-profile]:hover,
    .sidebar-user flux\:sidebar\.profile:hover,
    .sidebar-user .flux-sidebar-profile:hover {
        background-color: #f3f4f6 !important; /* gray-100 */
        color: #111827 !important; /* dark gray */
        border-radius: 8px !important;
    }

    .sidebar-user [data-flux-profile]:hover *,
    .sidebar-user button[data-test="sidebar-menu-button"]:hover *,
    .sidebar-user [data-flux-sidebar-profile]:hover *,
    .sidebar-user flux\:sidebar\.profile:hover *,
    .sidebar-user .flux-sidebar-profile:hover * {
        color: #111827 !important;
    }

    /* ==========================================
       THEME 2: SIDEBAR ADMIN (PENGELOLA - DARK MIDNIGHT & VIOLET)
       ========================================== */
    /* Sidebar Item Default */
    .sidebar-admin [data-flux-sidebar-item], 
    .sidebar-admin flux\:sidebar\.item {
        color: #94a3b8 !important; /* slate-400 */
        margin-bottom: 2px !important;
        transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1) !important;
        display: flex;
        font-weight: 500 !important;
        border-radius: 8px !important;
    }
    
    /* Hover State */
    .sidebar-admin [data-flux-sidebar-item]:hover,
    .sidebar-admin flux\:sidebar\.item:hover {
        background-color: #1e293b !important; /* slate-800 */
        color: #ffffff !important;
        transform: translateX(4px);
    }
    
    /* Active State (Selected Menu) */
    .sidebar-admin [data-flux-sidebar-item][current],
    .sidebar-admin flux\:sidebar\.item[current] {
        background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%) !important; /* Indigo Gradient */
        color: #ffffff !important;
        box-shadow: 0 4px 12px rgba(79, 70, 229, 0.25) !important;
    }

    /* Group Headings */
    .sidebar-admin [data-flux-sidebar-group] h2, 
    .sidebar-admin flux\:sidebar\.group h2 {
        color: #818cf8 !important; /* indigo-400 */
        font-weight: 700 !important;
        text-transform: uppercase !important;
        letter-spacing: 0.075em !important;
        font-size: 0.65rem !important;
        margin-top: 1.5rem !important;
        padding-left: 0.5rem;
    }

    /* Profile Section Divider */
    .sidebar-admin [data-test="sidebar-menu-button"] {
        border-top: 1px solid #1e293b !important;
        margin-top: 8px !important;
        padding-top: 12px !important;
        transition: all 0.2s ease !important;
    }

    /* Profile Hover */
    .sidebar-admin [data-flux-profile]:hover,
    .sidebar-admin button[data-test="sidebar-menu-button"]:hover,
    .sidebar-admin [data-flux-sidebar-profile]:hover,
    .sidebar-admin flux\:sidebar\.profile:hover,
    .sidebar-admin .flux-sidebar-profile:hover {
        background-color: #27272a !important; /* zinc-800 */
        color: #ffffff !important;
        border-radius: 8px !important;
    }

    .sidebar-admin [data-flux-profile]:hover *,
    .sidebar-admin button[data-test="sidebar-menu-button"]:hover *,
    .sidebar-admin [data-flux-sidebar-profile]:hover *,
    .sidebar-admin flux\:sidebar\.profile:hover *,
    .sidebar-admin .flux-sidebar-profile:hover * {
        color: #ffffff !important;
    }
</style>
    </body>
</html>
