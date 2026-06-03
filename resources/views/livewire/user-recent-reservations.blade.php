<?php

use App\Models\Reservation;
use Livewire\Volt\Component;

new class extends Component {
    public function with(): array
    {
        return [
            'recentReservations' => Reservation::with(['room'])
                ->where('user_id', auth()->id())
                ->latest()
                ->limit(5)
                ->get(),
        ];
    }
}; ?>

<div class="space-y-4">
    <div class="flex items-center justify-between">
        <div>
            <flux:heading size="lg">Riwayat Reservasi Terakhir</flux:heading>
            <flux:subheading>5 pengajuan reservasi terbaru Anda</flux:subheading>
        </div>
        <flux:button variant="ghost" href="{{ route('reservations.index') }}" wire:navigate icon="arrow-right" size="sm">
            Lihat Semua
        </flux:button>
    </div>

    <div class="bg-white border border-blue-100 shadow-lg shadow-blue-900/5 rounded-2xl overflow-hidden transition-all duration-300 hover:shadow-xl hover:shadow-blue-900/10 hover:-translate-y-0.5 mt-4">
        <div class="bg-gradient-to-r from-blue-600 via-blue-500 to-violet-500 px-6 py-4 border-b border-blue-100 flex items-center justify-between">
            <h3 class="font-bold text-white text-lg flex items-center gap-2">
                <flux:icon.document-text class="text-white/80" />
                Data Terbaru Anda
            </h3>
        </div>
        <div class="p-2 sm:p-4 overflow-x-auto">
            <flux:table>
        <flux:table.columns>
            <flux:table.column>Ruangan</flux:table.column>
            <flux:table.column>Kegiatan</flux:table.column>
            <flux:table.column>Waktu</flux:table.column>
            <flux:table.column>Status</flux:table.column>
        </flux:table.columns>

        <flux:table.rows>
            @forelse ($recentReservations as $reservation)
                <flux:table.row wire:key="{{ $reservation->id }}" class="transition-colors duration-200 hover:bg-blue-50/50">
                    <flux:table.cell>
                        <div class="text-sm font-medium">{{ $reservation->room->name }}</div>
                        <div class="text-xs text-neutral-400">{{ $reservation->room->building }}</div>
                    </flux:table.cell>
                    <flux:table.cell class="max-w-[200px] truncate text-sm">{{ $reservation->activity_name }}</flux:table.cell>
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
                </flux:table.row>
            @empty
                <flux:table.row>
                    <flux:table.cell colspan="4" class="text-center py-8 text-neutral-500">
                        Anda belum pernah melakukan reservasi ruangan.
                    </flux:table.cell>
                </flux:table.row>
            @endforelse
        </flux:table.rows>
            </flux:table>
        </div>
    </div>
</div>