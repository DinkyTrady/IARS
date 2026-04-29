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
            <flux:heading size="xl">Jadwal Reservasi</flux:heading>
            <flux:subheading>Lihat semua reservasi ruangan yang telah disetujui</flux:subheading>
        </div>

        <div class="flex gap-2">
            <flux:button variant="ghost" size="sm" icon="arrow-path" wire:click="resetFilters">
                Reset Filter
            </flux:button>
        </div>
    </div>

    {{-- Stats Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <flux:card>
            <div class="flex items-center gap-3">
                <div class="p-3 bg-blue-100 rounded-lg">
                    <flux:icon.calendar class="size-6 text-blue-600" />
                </div>
                <div>
                    <div class="text-2xl font-bold text-neutral-900">{{ $stats['today'] }}</div>
                    <div class="text-sm text-neutral-500">Reservasi Hari Ini</div>
                </div>
            </div>
        </flux:card>

        <flux:card>
            <div class="flex items-center gap-3">
                <div class="p-3 bg-green-100 rounded-lg">
                    <flux:icon.calendar-days class="size-6 text-green-600" />
                </div>
                <div>
                    <div class="text-2xl font-bold text-neutral-900">{{ $stats['thisWeek'] }}</div>
                    <div class="text-sm text-neutral-500">Minggu Ini</div>
                </div>
            </div>
        </flux:card>

        <flux:card>
            <div class="flex items-center gap-3">
                <div class="p-3 bg-purple-100 rounded-lg">
                    <flux:icon.calendar class="size-6 text-purple-600" />
                </div>
                <div>
                    <div class="text-2xl font-bold text-neutral-900">{{ $stats['nextWeek'] }}</div>
                    <div class="text-sm text-neutral-500">Minggu Depan</div>
                </div>
            </div>
        </flux:card>
    </div>

    <flux:separator variant="subtle" />

    {{-- Filters --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <flux:field>
            <flux:label>Filter Ruangan</flux:label>
            <flux:select wire:model.live="filterRoom">
                <flux:select.option value="all">Semua Ruangan</flux:select.option>
                @foreach ($rooms as $room)
                    <flux:select.option value="{{ $room->id }}">
                        {{ $room->name }} - {{ $room->building }}
                    </flux:select.option>
                @endforeach
            </flux:select>
        </flux:field>

        <flux:field>
            <flux:label>Filter Periode</flux:label>
            <flux:select wire:model.live="filterWeek">
                <flux:select.option value="all">Semua Periode</flux:select.option>
                <flux:select.option value="current">Minggu Ini</flux:select.option>
                <flux:select.option value="next">Minggu Depan</flux:select.option>
            </flux:select>
        </flux:field>

        @if ($filterWeek === 'all')
            <flux:field>
                <flux:label>Tanggal Spesifik (Opsional)</flux:label>
                <flux:input type="date" wire:model.live="filterDate" placeholder="Pilih tanggal..." />
                <flux:description>Kosongkan untuk melihat semua tanggal</flux:description>
            </flux:field>
        @endif
    </div>

    {{-- Reservations Table --}}
    <flux:card>
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
                    <flux:table.row>
                        <flux:table.cell>
                            <div class="flex items-center gap-2">
                                <div>
                                    <div class="font-medium text-neutral-800">
                                        {{ Carbon::parse($reservation->date)->locale('id')->isoFormat('dddd, D MMMM Y') }}
                                    </div>
                                    <div class="text-sm text-neutral-500">
                                        <flux:icon.clock class="inline size-4" />
                                        {{ substr($reservation->start_time, 0, 5) }} - {{ substr($reservation->end_time, 0, 5) }}
                                    </div>
                                </div>
                                @if (Carbon::parse($reservation->date)->isToday())
                                    <flux:badge color="blue" size="sm">Hari Ini</flux:badge>
                                @elseif (Carbon::parse($reservation->date)->isTomorrow())
                                    <flux:badge color="green" size="sm">Besok</flux:badge>
                                @endif
                            </div>
                        </flux:table.cell>

                        <flux:table.cell>
                            <div class="font-medium">{{ $reservation->room->name }}</div>
                            <div class="text-xs text-neutral-500">
                                {{ $reservation->room->building }}, Lt. {{ $reservation->room->floor }}
                            </div>
                        </flux:table.cell>

                        <flux:table.cell>
                            <div class="font-medium text-neutral-800">{{ $reservation->activity_name }}</div>
                        </flux:table.cell>

                        <flux:table.cell>
                            <div class="text-sm">{{ $reservation->user->name }}</div>
                            <div class="text-xs text-neutral-500">{{ $reservation->user->email }}</div>
                        </flux:table.cell>

                        <flux:table.cell>
                            <div class="max-w-[300px] text-sm text-neutral-600">
                                {{ $reservation->description ?: '-' }}
                            </div>
                        </flux:table.cell>
                    </flux:table.row>
                @empty
                    <flux:table.row>
                        <flux:table.cell colspan="5" class="text-center py-8 text-neutral-500">
                            <div class="flex flex-col items-center gap-2">
                                <flux:icon.calendar class="size-12 text-neutral-300" />
                                <div>Tidak ada reservasi yang disetujui untuk filter ini.</div>
                            </div>
                        </flux:table.cell>
                    </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>

        {{-- Pagination --}}
        <div class="mt-4">
            {{ $reservations->links() }}
        </div>
    </flux:card>
</div>
