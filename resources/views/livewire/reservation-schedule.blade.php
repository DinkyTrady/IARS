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

<div class="space-y-6">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row justify-between sm:items-end gap-4">
        <div>
            <h1 class="text-2xl font-extrabold text-zinc-900 tracking-tight">Jadwal Reservasi</h1>
            <p class="text-xs text-zinc-500 font-semibold mt-0.5">Lihat semua reservasi ruangan yang telah disetujui oleh pengelola.</p>
        </div>

        <div class="flex gap-2">
            <flux:button variant="ghost" size="sm" icon="arrow-path" wire:click="resetFilters" class="font-bold border border-zinc-200 rounded-xl cursor-pointer">
                Reset Filter
            </flux:button>
        </div>
    </div>

    {{-- Stats Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
        <div class="bg-white border border-zinc-200 rounded-2xl p-5 shadow-sm flex items-center gap-4 relative overflow-hidden">
            <div class="absolute left-0 top-0 bottom-0 w-1 bg-blue-600"></div>
            <div class="p-3 bg-blue-50 rounded-xl text-blue-600 flex items-center justify-center shrink-0">
                <flux:icon.calendar class="size-6 text-blue-600" />
            </div>
            <div>
                <div class="text-[11px] font-bold text-zinc-400 uppercase tracking-wider">Reservasi Hari Ini</div>
                <div class="text-2xl font-extrabold text-zinc-800 mt-0.5">{{ $stats['today'] }}</div>
            </div>
        </div>

        <div class="bg-white border border-zinc-200 rounded-2xl p-5 shadow-sm flex items-center gap-4 relative overflow-hidden">
            <div class="absolute left-0 top-0 bottom-0 w-1 bg-green-500"></div>
            <div class="p-3 bg-green-50 rounded-xl text-green-600 flex items-center justify-center shrink-0">
                <flux:icon.calendar-days class="size-6 text-green-600" />
            </div>
            <div>
                <div class="text-[11px] font-bold text-zinc-400 uppercase tracking-wider">Minggu Ini</div>
                <div class="text-2xl font-extrabold text-zinc-800 mt-0.5">{{ $stats['thisWeek'] }}</div>
            </div>
        </div>

        <div class="bg-white border border-zinc-200 rounded-2xl p-5 shadow-sm flex items-center gap-4 relative overflow-hidden">
            <div class="absolute left-0 top-0 bottom-0 w-1 bg-purple-500"></div>
            <div class="p-3 bg-purple-50 rounded-xl text-purple-600 flex items-center justify-center shrink-0">
                <flux:icon.calendar class="size-6 text-purple-600" />
            </div>
            <div>
                <div class="text-[11px] font-bold text-zinc-400 uppercase tracking-wider">Minggu Depan</div>
                <div class="text-2xl font-extrabold text-zinc-800 mt-0.5">{{ $stats['nextWeek'] }}</div>
            </div>
        </div>
    </div>

    <flux:separator variant="subtle" />

    {{-- Filters --}}
    <div class="bg-white border border-zinc-200 shadow-sm rounded-2xl p-6 grid grid-cols-1 sm:grid-cols-3 gap-6">
        <flux:field>
            <flux:label class="font-bold text-zinc-700">Filter Ruangan</flux:label>
            <flux:select wire:model.live="filterRoom" class="rounded-xl border-zinc-200">
                <flux:select.option value="all">Semua Ruangan</flux:select.option>
                @foreach ($rooms as $room)
                    <flux:select.option value="{{ $room->id }}">
                        {{ $room->name }} - {{ $room->building }}
                    </flux:select.option>
                @endforeach
            </flux:select>
        </flux:field>

        <flux:field>
            <flux:label class="font-bold text-zinc-700">Filter Periode</flux:label>
            <flux:select wire:model.live="filterWeek" class="rounded-xl border-zinc-200">
                <flux:select.option value="all">Semua Periode</flux:select.option>
                <flux:select.option value="current">Minggu Ini</flux:select.option>
                <flux:select.option value="next">Minggu Depan</flux:select.option>
            </flux:select>
        </flux:field>

        @if ($filterWeek === 'all')
            <flux:field>
                <flux:label class="font-bold text-zinc-700">Tanggal Spesifik <flux:badge size="sm" variant="ghost" class="text-zinc-500 font-bold ml-1">Opsional</flux:badge></flux:label>
                <flux:input type="date" wire:model.live="filterDate" placeholder="Pilih tanggal..." class="rounded-xl border-zinc-200" />
            </flux:field>
        @endif
    </div>

    {{-- Reservations Table --}}
    <div class="bg-white border border-zinc-200 shadow-sm rounded-2xl overflow-hidden">
        <div class="p-2 sm:p-4 overflow-x-auto">
            <flux:table>
                <flux:table.columns>
                    <flux:table.column>Tanggal & Waktu</flux:table.column>
                    <flux:table.column>Ruangan</flux:table.column>
                    <flux:table.column>Kegiatan</flux:table.column>
                    <flux:table.column>Pemohon</flux:table.column>
                    <flux:table.column>Deskripsi</flux:table.column>
                </flux:table.columns>

                <flux:table.rows>
                    @forelse ($reservations as $reservation)
                        <flux:table.row class="transition-colors duration-200 hover:bg-zinc-50/50">
                            <flux:table.cell>
                                <div class="flex items-center gap-2">
                                    <div>
                                        <div class="font-bold text-zinc-800">
                                            {{ Carbon::parse($reservation->date)->locale('id')->isoFormat('dddd, D MMMM Y') }}
                                        </div>
                                        <div class="text-xs font-semibold text-zinc-500 mt-1 flex items-center gap-1">
                                            <flux:icon.clock class="inline size-4 text-zinc-400" />
                                            <span>{{ substr($reservation->start_time, 0, 5) }} - {{ substr($reservation->end_time, 0, 5) }}</span>
                                        </div>
                                    </div>
                                    @if (Carbon::parse($reservation->date)->isToday())
                                        <flux:badge color="blue" size="sm" class="text-[10px] py-0.5 px-1.5 font-bold uppercase tracking-wide">Hari Ini</flux:badge>
                                    @elseif (Carbon::parse($reservation->date)->isTomorrow())
                                        <flux:badge color="green" size="sm" class="text-[10px] py-0.5 px-1.5 font-bold uppercase tracking-wide">Besok</flux:badge>
                                    @endif
                                </div>
                            </flux:table.cell>

                            <flux:table.cell>
                                <div class="font-bold text-zinc-800 text-sm">{{ $reservation->room->name }}</div>
                                <div class="text-xs text-neutral-400 font-semibold mt-0.5">
                                    {{ $reservation->room->building }}, Lt. {{ $reservation->room->floor }}
                                </div>
                            </flux:table.cell>

                            <flux:table.cell>
                                <div class="font-bold text-zinc-800 text-sm">{{ $reservation->activity_name }}</div>
                            </flux:table.cell>

                            <flux:table.cell>
                                <div class="font-bold text-zinc-800 text-sm">{{ $reservation->user->name }}</div>
                                <div class="text-xs text-zinc-400 font-semibold mt-0.5">{{ $reservation->user->email }}</div>
                            </flux:table.cell>

                            <flux:table.cell>
                                <div class="max-w-[300px] text-xs font-medium text-zinc-500 leading-relaxed truncate" title="{{ $reservation->description }}">
                                    {{ $reservation->description ?: '-' }}
                                </div>
                            </flux:table.cell>
                        </flux:table.row>
                    @empty
                        <flux:table.row>
                            <flux:table.cell colspan="5" class="text-center py-10 text-neutral-500">
                                <div class="flex flex-col items-center gap-2">
                                    <flux:icon.calendar class="size-12 text-zinc-300" />
                                    <div class="font-bold text-sm text-zinc-600">Tidak ada reservasi yang disetujui untuk filter ini.</div>
                                </div>
                            </flux:table.cell>
                        </flux:table.row>
                    @endforelse
                </flux:table.rows>
            </flux:table>
        </div>

        {{-- Pagination --}}
        <div class="p-4 border-t border-zinc-200 bg-zinc-50/50">
            {{ $reservations->links() }}
        </div>
    </div>
</div>
</div>
