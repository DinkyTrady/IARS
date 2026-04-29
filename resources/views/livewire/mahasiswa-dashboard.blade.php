<?php

use App\Models\Reservation;
use App\Models\Room;
use Livewire\Volt\Component;

new class extends Component {
    public function with(): array
    {
        $user = auth()->user();
        
        return [
            'stats' => [
                'pending' => $user->reservations()->where('status', 'pending')->count(),
                'approved' => $user->reservations()->where('status', 'approved')->count(),
                'total' => $user->reservations()->count(),
            ],
            'upcoming_reservations' => $user->reservations()
                ->with('room')
                ->where('status', 'approved')
                ->where('date', '>=', now()->format('Y-m-d'))
                ->orderBy('date')
                ->orderBy('start_time')
                ->take(3)
                ->get(),
            'available_rooms_count' => Room::where('status', 'available')->count(),
        ];
    }
}; ?>

<div class="space-y-8">
    <!-- Hero Section with Welcome Message -->
    <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-blue-600 via-indigo-600 to-purple-600 p-8 text-white shadow-lg">
        <div class="relative z-10 flex flex-col md:flex-row items-center justify-between gap-6">
            <div class="space-y-2 text-center md:text-left">
                <flux:heading size="xl" class="!text-white font-black">Halo, {{ auth()->user()->name }}! 👋</flux:heading>
                <flux:text class="text-blue-100 text-lg opacity-90">Senang melihat Anda kembali. Siap untuk produktif hari ini?</flux:text>
            </div>
            <div class="hidden md:flex items-center gap-4 bg-white/10 backdrop-blur-sm p-4 rounded-xl border border-white/20">
                <div class="text-center px-4 border-r border-white/20">
                    <div class="text-2xl font-black">{{ $stats['total'] }}</div>
                    <div class="text-[10px] uppercase font-bold tracking-wider opacity-70">Reservasi</div>
                </div>
                <div class="text-center px-4">
                    <div class="text-2xl font-black">{{ $available_rooms_count }}</div>
                    <div class="text-[10px] uppercase font-bold tracking-wider opacity-70">Ruangan Ready</div>
                </div>
            </div>
        </div>
        <!-- Decorative blobs -->
        <div class="absolute -right-10 -bottom-10 w-64 h-64 bg-white/10 rounded-full blur-3xl"></div>
        <div class="absolute -left-10 -top-10 w-32 h-32 bg-purple-400/20 rounded-full blur-2xl"></div>
    </div>

    <!-- Quick Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <flux:card class="relative overflow-hidden border-l-4 border-l-yellow-400">
            <div class="flex items-center gap-4">
                <div class="p-3 rounded-xl bg-yellow-50 text-yellow-600">
                    <flux:icon.clock variant="outline" />
                </div>
                <div>
                    <flux:text size="sm" variant="subtle" class="font-bold uppercase tracking-tighter">Menunggu</flux:text>
                    <flux:heading size="xl" class="leading-none">{{ $stats['pending'] }}</flux:heading>
                </div>
            </div>
        </flux:card>

        <flux:card class="relative overflow-hidden border-l-4 border-l-green-500">
            <div class="flex items-center gap-4">
                <div class="p-3 rounded-xl bg-green-50 text-green-600">
                    <flux:icon.check-circle variant="outline" />
                </div>
                <div>
                    <flux:text size="sm" variant="subtle" class="font-bold uppercase tracking-tighter text-green-600">Disetujui</flux:text>
                    <flux:heading size="xl" class="leading-none text-green-700">{{ $stats['approved'] }}</flux:heading>
                </div>
            </div>
        </flux:card>

        <flux:card class="relative overflow-hidden border-l-4 border-l-blue-500">
            <div class="flex items-center gap-4">
                <div class="p-3 rounded-xl bg-blue-50 text-blue-600">
                    <flux:icon.calendar variant="outline" />
                </div>
                <div>
                    <flux:text size="sm" variant="subtle" class="font-bold uppercase tracking-tighter text-blue-600">Total Pengajuan</flux:text>
                    <flux:heading size="xl" class="leading-none text-blue-700">{{ $stats['total'] }}</flux:heading>
                </div>
            </div>
        </flux:card>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Upcoming Schedule -->
        <div class="lg:col-span-1 space-y-6">
            <div class="flex items-center justify-between">
                <flux:heading size="lg" class="font-bold">Jadwal Mendatang</flux:heading>
                <flux:badge variant="pill" size="sm" color="indigo">{{ count($upcoming_reservations) }} Aktif</flux:badge>
            </div>
            
            <div class="space-y-4">
                @forelse ($upcoming_reservations as $res)
                    <div class="group relative bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-2xl p-4 transition-all hover:shadow-md hover:border-indigo-300">
                        <div class="flex gap-4 items-center">
                            <div class="flex flex-col items-center justify-center min-w-[60px] h-[60px] rounded-xl bg-gradient-to-b from-indigo-50 to-white border border-indigo-100 text-indigo-700 shadow-sm group-hover:from-indigo-600 group-hover:to-indigo-700 group-hover:text-white transition-colors">
                                <span class="text-[10px] font-black uppercase tracking-tighter leading-none mb-1 opacity-70">{{ \Carbon\Carbon::parse($res->date)->format('M') }}</span>
                                <span class="text-xl font-black leading-none">{{ \Carbon\Carbon::parse($res->date)->format('d') }}</span>
                            </div>
                            <div class="flex-1 min-w-0">
                                <flux:heading size="sm" class="truncate font-bold mb-1">{{ $res->activity_name }}</flux:heading>
                                <div class="flex items-center gap-2 text-xs text-zinc-500">
                                    <flux:icon.map-pin variant="mini" class="opacity-50" />
                                    <span class="truncate">{{ $res->room->name }}</span>
                                </div>
                                <div class="flex items-center gap-2 text-xs text-zinc-500 mt-1">
                                    <flux:icon.clock variant="mini" class="opacity-50" />
                                    <span>{{ substr($res->start_time, 0, 5) }} WIB</span>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="flex flex-col items-center justify-center p-8 bg-zinc-50 dark:bg-zinc-800/50 rounded-2xl border-2 border-dashed border-zinc-200 dark:border-zinc-800">
                        <flux:icon.calendar class="text-zinc-300 mb-2" />
                        <flux:text variant="subtle" class="text-sm">Tidak ada jadwal terdekat.</flux:text>
                    </div>
                @endforelse
            </div>
            
            @if(count($upcoming_reservations) > 0)
                <flux:button variant="ghost" size="sm" class="w-full text-indigo-600 font-bold" :href="route('reservations.index')" wire:navigate>
                    Lihat Semua Histori →
                </flux:button>
            @endif
        </div>

        <!-- Room Quick Access -->
        <div class="lg:col-span-2 space-y-6">
            <div class="flex justify-between items-center">
                <flux:heading size="lg" class="font-bold">Pilih Ruangan</flux:heading>
                <div class="text-xs text-zinc-500 bg-zinc-100 dark:bg-zinc-800 px-3 py-1 rounded-full border border-zinc-200 dark:border-zinc-700">
                    Menampilkan {{ $available_rooms_count }} Ruangan Tersedia
                </div>
            </div>
            
            <livewire:room-list />
        </div>
    </div>
</div>
