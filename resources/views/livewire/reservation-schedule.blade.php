<?php

use App\Models\Reservation;
use App\Models\Room;
use Illuminate\Support\Carbon;
use Livewire\Volt\Component;
use Livewire\WithPagination;

new class extends Component {
    use WithPagination;

    public string $filterRoom = 'all';
    public string $filterDate = '';
    public string $filterWeek = 'all';
    public string $viewMode = 'list';

    public function mount(): void
    {
        // defaults sudah di-set di property declaration
    }

    public function with(): array
    {
        $query = Reservation::with(['user', 'room'])
            ->where('status', 'approved')
            ->orderBy('date')
            ->orderBy('start_time');

        // Filter by room
        if ($this->filterRoom !== 'all') {
            $query->where('room_id', $this->filterRoom);
        }

        // Filter by week
        if ($this->filterWeek === 'current') {
            $query->whereBetween('date', [
                now()->startOfWeek(),
                now()->endOfWeek()
            ]);
        } elseif ($this->filterWeek === 'next') {
            $query->whereBetween('date', [
                now()->addWeek()->startOfWeek(),
                now()->addWeek()->endOfWeek()
            ]);
        }
        // Jika 'all', tidak ada filter tanggal otomatis

        // Filter by specific date (hanya jika user mengisi tanggal)
        if ($this->filterDate && $this->filterWeek === 'all') {
            $query->whereDate('date', $this->filterDate);
        }

        return [
            'reservations' => $query->paginate(20),
            'rooms' => Room::where('status', 'available')->orderBy('name')->get(),
            'stats' => [
                'today' => Reservation::where('status', 'approved')
                    ->whereDate('date', now())
                    ->count(),
                'thisWeek' => Reservation::where('status', 'approved')
                    ->whereBetween('date', [now()->startOfWeek(), now()->endOfWeek()])
                    ->count(),
                'nextWeek' => Reservation::where('status', 'approved')
                    ->whereBetween('date', [now()->addWeek()->startOfWeek(), now()->addWeek()->endOfWeek()])
                    ->count(),
            ],
        ];
    }

    public function resetFilters(): void
    {
        $this->filterRoom = 'all';
        $this->filterDate = '';
        $this->filterWeek = 'all';
        $this->resetPage();
    }
}; ?>

<div class="max-w-[1400px] mx-auto px-4 sm:px-6 space-y-6">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row justify-between sm:items-end gap-4">
        <div>
            <h1 class="text-[32px] font-bold text-slate-900 tracking-[-0.02em] leading-tight">Jadwal Reservasi</h1>
            <p class="text-[15px] text-slate-500 mt-1">Lihat semua reservasi ruangan yang telah disetujui oleh pengelola.</p>
        </div>

        <div>
            <button wire:click="resetFilters" class="inline-flex items-center gap-2 px-4 h-10 bg-white border border-slate-200 text-[13px] font-semibold text-slate-700 rounded-[12px] hover:bg-slate-50 hover:text-slate-900 transition-colors shadow-sm outline-none">
                <flux:icon.arrow-path class="size-4" />
                Reset Filter
            </button>
        </div>
    </div>

    {{-- Compact Stats Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
        <div class="h-[88px] bg-white rounded-[20px] shadow-sm border border-slate-100 flex items-center px-5 gap-4 transition-all duration-300 hover:shadow-xl hover:shadow-blue-900/10 hover:-translate-y-1 group">
            <div class="size-12 rounded-full bg-blue-50 flex items-center justify-center shrink-0">
                <flux:icon.calendar-days class="size-6 text-blue-600" />
            </div>
            <div>
                <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Reservasi Hari Ini</div>
                <div class="text-2xl font-bold text-slate-900 tracking-tight leading-none mt-1">{{ $stats['today'] }}</div>
            </div>
        </div>

        <div class="h-[88px] bg-white rounded-[20px] shadow-sm border border-slate-100 flex items-center px-5 gap-4 transition-all duration-300 hover:shadow-xl hover:shadow-blue-900/10 hover:-translate-y-1 group">
            <div class="size-12 rounded-full bg-green-50 flex items-center justify-center shrink-0">
                <flux:icon.calendar-days class="size-6 text-green-600" />
            </div>
            <div>
                <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Minggu Ini</div>
                <div class="text-2xl font-bold text-slate-900 tracking-tight leading-none mt-1">{{ $stats['thisWeek'] }}</div>
            </div>
        </div>

        <div class="h-[88px] bg-white rounded-[20px] shadow-sm border border-slate-100 flex items-center px-5 gap-4 transition-all duration-300 hover:shadow-xl hover:shadow-blue-900/10 hover:-translate-y-1 group">
            <div class="size-12 rounded-full bg-purple-50 flex items-center justify-center shrink-0">
                <flux:icon.calendar-days class="size-6 text-purple-600" />
            </div>
            <div>
                <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Minggu Depan</div>
                <div class="text-2xl font-bold text-slate-900 tracking-tight leading-none mt-1">{{ $stats['nextWeek'] }}</div>
            </div>
        </div>
    </div>

    {{-- Filters Toolbar --}}
    <div class="bg-white rounded-[24px] p-6 shadow-sm border border-slate-200 transition-all duration-300 hover:shadow-xl hover:shadow-blue-900/10 hover:-translate-y-1">
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
            <div class="space-y-2">
                <label class="block text-[13px] font-semibold text-slate-700">Filter Ruangan</label>
                <select wire:model.live="filterRoom" class="w-full h-12 rounded-[14px] border border-slate-200 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 text-sm font-medium text-slate-700 outline-none px-4 bg-white transition-all">
                    <option value="all">Semua Ruangan</option>
                    @foreach ($rooms as $room)
                        <option value="{{ $room->id }}">
                            {{ $room->name }} - {{ $room->building }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="space-y-2">
                <label class="block text-[13px] font-semibold text-slate-700">Filter Periode</label>
                <select wire:model.live="filterWeek" class="w-full h-12 rounded-[14px] border border-slate-200 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 text-sm font-medium text-slate-700 outline-none px-4 bg-white transition-all">
                    <option value="all">Semua Periode</option>
                    <option value="current">Minggu Ini</option>
                    <option value="next">Minggu Depan</option>
                </select>
            </div>

            @if ($filterWeek === 'all')
                <div class="space-y-2">
                    <label class="block text-[13px] font-semibold text-slate-700 flex items-center gap-2">
                        Tanggal Spesifik 
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-medium bg-slate-100 text-slate-500">Opsional</span>
                    </label>
                    <input type="date" wire:model.live="filterDate" class="w-full h-12 rounded-[14px] border border-slate-200 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 text-sm font-medium text-slate-700 outline-none px-4 bg-white transition-all" />
                </div>
            @endif
        </div>
    </div>

    {{-- Reservations Table --}}
    <div class="bg-white rounded-[20px] shadow-sm border border-slate-200 overflow-hidden transition-all duration-300 hover:shadow-xl hover:shadow-blue-900/10 hover:-translate-y-1">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="bg-slate-50 border-b border-slate-100 text-[13px] font-semibold text-slate-500">
                    <tr>
                        <th class="px-6 py-4">Tanggal & Waktu</th>
                        <th class="px-6 py-4">Ruangan</th>
                        <th class="px-6 py-4">Kegiatan</th>
                        <th class="px-6 py-4">Pemohon</th>
                        <th class="px-6 py-4">Deskripsi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($reservations as $reservation)
                        <tr class="hover:bg-slate-50 transition duration-200 group">
                            <td class="px-6 py-4">
                                <div class="flex items-start gap-2">
                                    <div>
                                        <div class="font-bold text-slate-900">
                                            {{ Carbon\Carbon::parse($reservation->date)->locale('id')->isoFormat('dddd, D MMMM Y') }}
                                        </div>
                                        <div class="text-[13px] text-slate-500 mt-1 flex items-center gap-1.5">
                                            <flux:icon.clock class="size-3.5" />
                                            <span>{{ substr($reservation->start_time, 0, 5) }} - {{ substr($reservation->end_time, 0, 5) }}</span>
                                        </div>
                                    </div>
                                    @if (Carbon\Carbon::parse($reservation->date)->isToday())
                                        <span class="inline-flex items-center px-2 py-0.5 rounded border border-blue-200 bg-blue-50 text-blue-700 text-[10px] font-bold uppercase tracking-wide">Hari Ini</span>
                                    @elseif (Carbon\Carbon::parse($reservation->date)->isTomorrow())
                                        <span class="inline-flex items-center px-2 py-0.5 rounded border border-green-200 bg-green-50 text-green-700 text-[10px] font-bold uppercase tracking-wide">Besok</span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="font-bold text-slate-900">{{ $reservation->room->name }}</div>
                                <div class="text-[13px] text-slate-500 mt-0.5">
                                    {{ $reservation->room->building }}, Lt. {{ $reservation->room->floor }}
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="font-medium text-slate-900">{{ $reservation->activity_name }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="font-bold text-slate-900">{{ $reservation->user->name }}</div>
                                <div class="text-[13px] text-slate-500 mt-0.5">{{ $reservation->user->email }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="max-w-[250px] text-[13px] text-slate-500 truncate" title="{{ $reservation->description }}">
                                    {{ $reservation->description ?: '-' }}
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-16 text-center">
                                <div class="flex flex-col items-center justify-center">
                                    <div class="size-16 rounded-full bg-slate-50 flex items-center justify-center mb-4 text-3xl">
                                        📅
                                    </div>
                                    <h3 class="text-[15px] font-bold text-slate-900">Tidak ada jadwal reservasi</h3>
                                    <p class="text-sm text-slate-500 mt-1 max-w-sm mx-auto">
                                        Belum ada reservasi ruangan yang disetujui untuk filter ini.
                                    </p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($reservations->hasPages())
            <div class="px-6 py-4 border-t border-slate-100 bg-slate-50/50">
                {{ $reservations->links() }}
            </div>
        @endif
    </div>
</div>
