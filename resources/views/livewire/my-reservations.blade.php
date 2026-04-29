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

        if ($this->statusFilter !== 'all') {
            $query->where('status', $this->statusFilter);
        }

        return [
            'reservations' => $query->paginate(10),
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
            <flux:heading size="xl" class="text-transparent bg-clip-text bg-gradient-to-r from-blue-600 to-violet-600 font-bold">Riwayat Reservasi Saya</flux:heading>
            <flux:subheading>Pantau status pengajuan peminjaman ruangan Anda di sini.</flux:subheading>
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

    <div class="bg-white border border-blue-100 shadow-lg shadow-blue-900/5 rounded-2xl overflow-hidden transition-all duration-300 hover:shadow-xl hover:shadow-blue-900/10 hover:-translate-y-0.5">
        <div class="bg-gradient-to-r from-blue-600 via-blue-500 to-violet-500 px-6 py-4 border-b border-blue-100 flex items-center justify-between">
            <h3 class="font-bold text-white text-lg flex items-center gap-2">
                <flux:icon.calendar class="text-white/80" />
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
        <div class="p-4 border-t border-blue-100 bg-slate-50/50">
            {{ $reservations->links() }}
        </div>
    </div>
</div>