<?php

use App\Models\Reservation;
use Livewire\Volt\Component;
use Livewire\WithPagination;
use Flux\Flux;

new class extends Component {
    use WithPagination;

    public string $statusFilter = 'all';

    public function with(): array
    {
        // Mengambil data reservasi khusus milik user yang sedang login
        $query = Reservation::where('user_id', auth()->id())
            ->with('room')
            ->latest();

        $stats = [
            'total' => Reservation::where('user_id', auth()->id())->count(),
            'approved' => Reservation::where('user_id', auth()->id())->where('status', 'approved')->count(),
            'pending' => Reservation::where('user_id', auth()->id())->where('status', 'pending')->count(),
        ];

        if ($this->statusFilter !== 'all') {
            $query->where('status', $this->statusFilter);
        }

        return [
            'reservations' => $query->paginate(10),
            'stats' => $stats,
        ];
    }

    public function cancel(int $id): void
    {
        $reservation = Reservation::findOrFail($id);

        $this->authorize('cancel', $reservation);

        $reservation->update(['status' => 'canceled']);
        Flux::toast('Reservasi telah dibatalkan.', variant: 'success');
    }
}; ?>

<div class="space-y-6">
    <div class="flex flex-col sm:flex-row justify-between sm:items-end gap-4">
        <div>
            <h1 class="text-2xl font-extrabold text-zinc-900 tracking-tight">Riwayat Reservasi Saya</h1>
            <p class="text-sm text-zinc-500 mt-1">Pantau status pengajuan peminjaman ruangan Anda di sini.</p>
        </div>

        <div class="w-full sm:w-48">
            <flux:select wire:model.live="statusFilter" size="sm" placeholder="Filter Status">
                <flux:select.option value="all">Semua Status</flux:select.option>
                <flux:select.option value="pending">Menunggu (Pending)</flux:select.option>
                <flux:select.option value="approved">Disetujui (Approved)</flux:select.option>
                <flux:select.option value="rejected">Ditolak (Rejected)</flux:select.option>
                <flux:select.option value="canceled">Dibatalkan (Canceled)</flux:select.option>
            </flux:select>
        </div>
    </div>
    
    {{-- Quick Stats Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white border border-zinc-200 rounded-2xl p-5 shadow-sm flex items-center gap-4 relative overflow-hidden">
            <div class="absolute left-0 top-0 bottom-0 w-1 bg-zinc-400"></div>
            <div class="p-3 bg-zinc-100 rounded-xl text-zinc-500 flex items-center justify-center shrink-0">
                <flux:icon.calendar class="size-6 text-zinc-500" />
            </div>
            <div>
                <div class="text-[11px] font-bold text-zinc-400 uppercase tracking-wider">Total Pengajuan</div>
                <div class="text-2xl font-extrabold text-zinc-800 mt-0.5">{{ $stats['total'] }}</div>
            </div>
        </div>

        <div class="bg-white border border-zinc-200 rounded-2xl p-5 shadow-sm flex items-center gap-4 relative overflow-hidden">
            <div class="absolute left-0 top-0 bottom-0 w-1 bg-green-500"></div>
            <div class="p-3 bg-green-50 rounded-xl text-green-600 flex items-center justify-center shrink-0">
                <flux:icon.check-circle class="size-6 text-green-600" />
            </div>
            <div>
                <div class="text-[11px] font-bold text-zinc-400 uppercase tracking-wider">Disetujui</div>
                <div class="text-2xl font-extrabold text-zinc-800 mt-0.5">{{ $stats['approved'] }}</div>
            </div>
        </div>

        <div class="bg-white border border-zinc-200 rounded-2xl p-5 shadow-sm flex items-center gap-4 relative overflow-hidden">
            <div class="absolute left-0 top-0 bottom-0 w-1 bg-yellow-500"></div>
            <div class="p-3 bg-yellow-50 rounded-xl text-yellow-600 flex items-center justify-center shrink-0">
                <flux:icon.clock class="size-6 text-yellow-600" />
            </div>
            <div>
                <div class="text-[11px] font-bold text-zinc-400 uppercase tracking-wider">Menunggu Persetujuan</div>
                <div class="text-2xl font-extrabold text-zinc-800 mt-0.5">{{ $stats['pending'] }}</div>
            </div>
        </div>
    </div>

    <div class="bg-white border border-zinc-200 shadow-sm rounded-2xl overflow-hidden">
        <div class="bg-zinc-50 px-6 py-4 border-b border-zinc-200 flex items-center justify-between">
            <h3 class="font-bold text-zinc-800 text-md flex items-center gap-2">
                <flux:icon.calendar class="text-blue-600" variant="mini" />
                Daftar Reservasi Saya
            </h3>
        </div>
        <div class="p-2 sm:p-4 overflow-x-auto">
            <flux:table>
        <flux:table.columns>
            <flux:table.column>Ruangan</flux:table.column>
            <flux:table.column>Kegiatan</flux:table.column>
            <flux:table.column>Waktu</flux:table.column>
            <flux:table.column>Status</flux:table.column>
            <flux:table.column>Aksi</flux:table.column>
        </flux:table.columns>

        <flux:table.rows>
            @forelse ($reservations as $reservation)
                <flux:table.row wire:key="{{ $reservation->id }}" class="transition-colors duration-200 hover:bg-blue-50/50">
                    <flux:table.cell>
                        <div class="font-medium">{{ $reservation->room->name }}</div>
                        <div class="text-xs text-neutral-500">{{ $reservation->room->building }}</div>
                    </flux:table.cell>
                    <flux:table.cell>
                        <div class="max-w-[200px] truncate" title="{{ $reservation->description }}">
                            {{ $reservation->activity_name }}
                        </div>
                    </flux:table.cell>
                    <flux:table.cell>
                        <div class="text-sm">{{ $reservation->date->format('d M Y') }}</div>
                        <div class="text-xs text-neutral-500">{{ substr($reservation->start_time, 0, 5) }} -
                            {{ substr($reservation->end_time, 0, 5) }}</div>
                    </flux:table.cell>
                    <flux:table.cell>
                        @php
                            $color = match ($reservation->status) {
                                'approved' => 'green',
                                'pending' => 'yellow',
                                'rejected' => 'red',
                                'canceled' => 'neutral',
                                default => 'neutral',
                            };
                        @endphp
                        <flux:badge color="{{ $color }}" size="sm">{{ ucfirst($reservation->status) }}</flux:badge>

                        @if ($reservation->status === 'rejected' && $reservation->note)
                            <div class="text-[10px] text-red-600 mt-1 italic">Ket: {{ $reservation->note }}</div>
                        @endif
                    </flux:table.cell>
                    <flux:table.cell>
                        @if ($reservation->status === 'pending')
                            <flux:button variant="ghost" size="sm" color="red" wire:click="cancel({{ $reservation->id }})"
                                wire:confirm="Apakah Anda yakin ingin membatalkan reservasi ini?">
                                Batalkan
                            </flux:button>
                        @else
                            <span class="text-xs text-neutral-400 italic">-</span>
                        @endif
                    </flux:table.cell>
                </flux:table.row>
            @empty
                <flux:table.row>
                    <flux:table.cell colspan="5" class="text-center py-8 text-neutral-500">
                        Anda belum memiliki riwayat reservasi.
                    </flux:table.cell>
                </flux:table.row>
            @endforelse
        </flux:table.rows>
            </flux:table>
        </div>
        <div class="p-4 border-t border-zinc-200 bg-zinc-50/50">
            {{ $reservations->links() }}
        </div>
    </div>
</div>