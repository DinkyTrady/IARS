<?php

use App\Models\Reservation;
use Livewire\Volt\Component;
use Flux\Flux;

new class extends Component {
    public function with(): array
    {
        return [
            'recentReservations' => Reservation::with(['user', 'room'])
                ->latest()
                ->limit(10)
                ->get(),
            'pendingCount' => Reservation::where('status', 'pending')->count(),
        ];
    }

    public function approve(int $id): void
    {
        Reservation::findOrFail($id)->update(['status' => 'approved']);
        Flux::toast('Reservasi disetujui.', variant: 'success');
    }
}; ?>

<div class="space-y-4">
    <div class="flex items-center justify-between">
        <div>
            <flux:heading size="lg">Reservasi Terbaru</flux:heading>
            <flux:subheading>10 pengajuan reservasi terbaru</flux:subheading>
        </div>
        @if($pendingCount > 0)
            <flux:button variant="primary" href="{{ route('admin.reservations') }}" wire:navigate icon="arrow-right" size="sm">
                Lihat Semua Pending ({{ $pendingCount }})
            </flux:button>
        @else
            <flux:button variant="ghost" href="{{ route('admin.reservations') }}" wire:navigate icon="arrow-right" size="sm">
                Lihat Semua
            </flux:button>
        @endif
    </div>

    <flux:table>
        <flux:table.columns>
            <flux:table.column>Pemohon</flux:table.column>
            <flux:table.column>Ruangan</flux:table.column>
            <flux:table.column>Kegiatan</flux:table.column>
            <flux:table.column>Waktu</flux:table.column>
            <flux:table.column>Status</flux:table.column>
            <flux:table.column>Aksi</flux:table.column>
        </flux:table.columns>

        <flux:table.rows>
            @forelse ($recentReservations as $reservation)
                <flux:table.row wire:key="{{ $reservation->id }}">
                    <flux:table.cell>
                        <div class="font-medium text-sm">{{ $reservation->user->name }}</div>
                        <div class="text-xs text-neutral-400">{{ $reservation->user->email }}</div>
                    </flux:table.cell>
                    <flux:table.cell>
                        <div class="text-sm font-medium">{{ $reservation->room->name }}</div>
                        <div class="text-xs text-neutral-400">{{ $reservation->room->building }}</div>
                    </flux:table.cell>
                    <flux:table.cell class="max-w-[180px] truncate text-sm">{{ $reservation->activity_name }}</flux:table.cell>
                    <flux:table.cell>
                        <div class="text-sm">{{ \Carbon\Carbon::parse($reservation->date)->format('d M Y') }}</div>
                        <div class="text-xs text-neutral-400">{{ substr($reservation->start_time, 0, 5) }} - {{ substr($reservation->end_time, 0, 5) }}</div>
                    </flux:table.cell>
                    <flux:table.cell>
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
                    </flux:table.cell>
                    <flux:table.cell>
                        @if($reservation->status === 'pending')
                            <flux:button variant="ghost" size="sm" class="text-green-600"
                                wire:click="approve({{ $reservation->id }})"
                                wire:confirm="Setujui reservasi ini?">
                                Setujui
                            </flux:button>
                        @else
                            <span class="text-xs text-neutral-400 italic">-</span>
                        @endif
                    </flux:table.cell>
                </flux:table.row>
            @empty
                <flux:table.row>
                    <flux:table.cell colspan="6" class="text-center py-8 text-neutral-500">
                        Belum ada pengajuan reservasi.
                    </flux:table.cell>
                </flux:table.row>
            @endforelse
        </flux:table.rows>
    </flux:table>
</div>
