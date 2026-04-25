<?php

use App\Models\Reservation;
use Livewire\Volt\Component;

new class extends Component {
    public function with(): array
    {
        return [
            'reservations' => auth()->user()->reservations()->latest()->get(),
        ];
    }
    
    public function cancel(int $id): void
    {
        $reservation = Reservation::findOrFail($id);
        
        if ($reservation->user_id !== auth()->id()) {
            return;
        }
        
        if ($reservation->status === 'pending') {
            $reservation->update(['status' => 'canceled']);
            \Flux\Flux::toast('Reservasi berhasil dibatalkan.', variant: 'success');
        }
    }
}; ?>

<div class="space-y-6">
    <div>
        <flux:heading size="xl">Reservasi Saya</flux:heading>
        <flux:subheading>Daftar pengajuan peminjaman ruangan Anda.</flux:subheading>
    </div>

    <flux:separator variant="subtle" />

    <flux:table>
        <flux:columns>
            <flux:column>Ruangan</flux:column>
            <flux:column>Kegiatan</flux:column>
            <flux:column>Tanggal & Waktu</flux:column>
            <flux:column>Status</flux:column>
            <flux:column>Aksi</flux:column>
        </flux:columns>

        <flux:rows>
            @forelse ($reservations as $reservation)
                <flux:row>
                    <flux:cell>
                        <div class="font-medium text-neutral-800">{{ $reservation->room->name }}</div>
                        <div class="text-xs text-neutral-500">{{ $reservation->room->building }}</div>
                    </flux:cell>
                    <flux:cell>{{ $reservation->activity_name }}</flux:cell>
                    <flux:cell>
                        <div class="text-sm">{{ \Carbon\Carbon::parse($reservation->date)->format('d M Y') }}</div>
                        <div class="text-xs text-neutral-500">{{ substr($reservation->start_time, 0, 5) }} - {{ substr($reservation->end_time, 0, 5) }}</div>
                    </flux:cell>
                    <flux:cell>
                        @php
                            $color = match($reservation->status) {
                                'approved' => 'green',
                                'pending' => 'yellow',
                                'rejected' => 'red',
                                'canceled' => 'neutral',
                                default => 'neutral',
                            };
                        @endphp
                        <flux:badge color="{{ $color }}" size="sm">{{ ucfirst($reservation->status) }}</flux:badge>
                    </flux:cell>
                    <flux:cell>
                        @if ($reservation->status === 'pending')
                            <flux:button variant="ghost" size="sm" wire:click="cancel({{ $reservation->id }})" wire:confirm="Apakah Anda yakin ingin membatalkan reservasi ini?">
                                Batalkan
                            </flux:button>
                        @endif
                    </flux:cell>
                </flux:row>
            @empty
                <flux:row>
                    <flux:cell colspan="5" class="text-center py-8 text-neutral-500">
                        Belum ada data reservasi.
                    </flux:cell>
                </flux:row>
            @endforelse
        </flux:rows>
    </flux:table>
</div>
