<x-layouts::app :title="__('Dashboard')">
    <div class="flex h-full w-full flex-1 flex-col gap-6">
        @if(auth()->user()->role === 'admin')
            {{-- =========================================================================
               ADMIN INTERFACE (Original Layout & Styles)
               ========================================================================= --}}
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                <div>
                    <h1 class="text-2xl tracking-tight text-transparent bg-clip-text bg-gradient-to-r from-blue-600 to-violet-600 font-bold">
                        Selamat Datang, <span class="text-blue-700 font-black">{{ auth()->user()->name }}</span>
                    </h1>
                    <flux:subheading>
                        Panel kontrol sistem informasi reservasi ruangan kampus.
                    </flux:subheading>
                </div>
            </div>

            <livewire:admin-dashboard-stats />
            <flux:separator variant="subtle" />

            <div class="flex-1 overflow-y-auto">
                <livewire:admin-recent-reservations />
            </div>
        @else
            {{-- =========================================================================
               USER INTERFACE (Brand New Structure & Solid Colors - Optimized UX)
               ========================================================================= --}}
            
            {{-- 1. Hero Card Section (Solid Theme, Left Blue Accent, Integrated Button) --}}
            <div class="bg-white border border-zinc-200 rounded-2xl p-6 shadow-sm flex flex-col md:flex-row justify-between items-start md:items-center gap-6 relative overflow-hidden">
                <div class="absolute left-0 top-0 bottom-0 w-1.5 bg-blue-600 rounded-s-2xl"></div>
                <div class="space-y-1 md:pl-2">
                    <h1 class="text-2xl font-extrabold text-zinc-900 tracking-tight">Selamat Datang, {{ auth()->user()->name }}!</h1>
                    <p class="text-sm text-zinc-500 font-medium">Sistem Informasi Reservasi Ruangan (IARS). Silakan pilih ruangan di bawah untuk kegiatan Anda.</p>
                </div>
                <flux:button 
                    icon="plus" 
                    class="bg-blue-600 hover:bg-blue-700 text-white border-none shadow-sm shadow-blue-500/10 px-5 py-2.5 rounded-xl font-semibold shrink-0 cursor-pointer" 
                    href="{{ route('reservations.create') }}" 
                    wire:navigate
                >
                    Reservasi Baru
                </flux:button>
            </div>

            {{-- 2. Two-Column Dashboard Layout (Left: Rooms, Right: Status & Guide) --}}
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Left: Room List -->
                <div class="lg:col-span-2 space-y-4">
                    <h2 class="text-lg font-bold text-zinc-900 flex items-center gap-2 px-1">
                        <span class="w-3 h-5 bg-blue-600 rounded-md inline-block"></span>
                        Daftar Ruangan Tersedia
                    </h2>
                    <livewire:room-list />
                </div>
                
                <!-- Right: Sidebar widgets (User Bookings & Guidelines) -->
                <div class="space-y-6">
                    <!-- Widget 1: Recent Booking Status -->
                    <div class="bg-white border border-zinc-200 rounded-2xl p-5 shadow-sm space-y-4">
                        <h3 class="font-bold text-zinc-800 text-sm flex items-center gap-2 border-b border-zinc-100 pb-3">
                            <flux:icon.calendar-days class="text-blue-600" variant="mini" />
                            Status Peminjaman Terbaru
                        </h3>
                        
                        @php
                            $myReservations = \App\Models\Reservation::where('user_id', auth()->id())
                                ->with('room')
                                ->latest()
                                ->take(3)
                                ->get();
                        @endphp
                        
                        <div class="space-y-3">
                            @forelse($myReservations as $res)
                                <div class="p-3 bg-zinc-50 rounded-xl border border-zinc-100 flex flex-col gap-1.5 transition-all hover:bg-blue-50/20">
                                    <div class="flex justify-between items-start gap-2">
                                        <span class="font-bold text-xs text-zinc-800 truncate max-w-[130px]">{{ $res->room->name }}</span>
                                        @php
                                            $badgeColor = match ($res->status) {
                                                'approved' => 'green',
                                                'pending' => 'yellow',
                                                'rejected' => 'red',
                                                'canceled' => 'neutral',
                                                default => 'neutral',
                                            };
                                        @endphp
                                        <flux:badge size="sm" color="{{ $badgeColor }}" class="text-[10px] py-0.5 px-1.5">{{ ucfirst($res->status) }}</flux:badge>
                                    </div>
                                    <div class="text-[11px] text-zinc-600 font-semibold truncate">{{ $res->activity_name }}</div>
                                    <div class="text-[10px] text-zinc-400 font-medium flex items-center gap-1">
                                        <flux:icon.clock variant="micro" class="text-zinc-400" />
                                        <span>{{ $res->date->format('d M Y') }} · {{ substr($res->start_time, 0, 5) }} - {{ substr($res->end_time, 0, 5) }}</span>
                                    </div>
                                </div>
                            @empty
                                <div class="text-center py-8 text-zinc-400 text-xs italic">
                                    Belum ada pengajuan reservasi.
                                </div>
                            @endforelse
                        </div>
                        
                        @if($myReservations->count() > 0)
                            <flux:button 
                                variant="ghost" 
                                class="w-full text-xs text-blue-600 hover:text-blue-700 font-semibold mt-2 cursor-pointer" 
                                href="{{ route('reservations.index') }}" 
                                wire:navigate
                            >
                                Lihat Semua Riwayat
                            </flux:button>
                        @endif
                    </div>

                    <!-- Widget 2: Quick Guide -->
                    <div class="bg-white border border-zinc-200 rounded-2xl p-5 shadow-sm space-y-3">
                        <h3 class="font-bold text-zinc-800 text-sm flex items-center gap-2 border-b border-zinc-100 pb-3">
                            <flux:icon.academic-cap class="text-blue-600" variant="mini" />
                            Panduan Pengajuan
                        </h3>
                        <ul class="text-xs text-zinc-500 space-y-2.5 pl-4 list-disc">
                            <li>Pilih ruangan yang bertanda hijau <span class="text-green-600 font-semibold">Tersedia</span>.</li>
                            <li>Isi form nama kegiatan, tanggal, dan waktu secara detail.</li>
                            <li>Jika ada bentrok dengan jadwal kuliah, sistem akan memberi tanda peringatan namun tetap dapat diajukan.</li>
                            <li>Pantau persetujuan pada menu <a href="{{ route('reservations.index') }}" wire:navigate class="text-blue-600 font-semibold hover:underline">Reservasi Saya</a>.</li>
                        </ul>
                    </div>
                </div>
            </div>
        @endif
    </div>
</x-layouts::app>
