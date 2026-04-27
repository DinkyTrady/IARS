<?php

use App\Models\Reservation;
use Livewire\Volt\Component;
use Flux\Flux;

new class extends Component {
    public ?Reservation $selectedReservation = null;
    public string $admin_note = '';

    public function with(): array
    {
        return [
            'reservations' => Reservation::with(['user', 'room'])->latest()->get(),
        ];
    }

    public function approve(int $id): void
    {
        $reservation = Reservation::findOrFail($id);
        $reservation->update(['status' => 'approved']);
        
        Flux::toast('Reservasi telah disetujui.', variant: 'success');
    }

    public function openRejectModal(int $id): void
    {
        $this->selectedReservation = Reservation::findOrFail($id);
        $this->admin_note = '';
        $this->modal('reject-modal')->show();
    }

    public function reject(): void
    {
        if ($this->selectedReservation) {
            $this->selectedReservation->update([
                'status' => 'rejected',
                'note' => $this->admin_note,
            ]);

            $this->modal('reject-modal')->close();
            $this->reset(['selectedReservation', 'admin_note']);
            
            Flux::toast('Reservasi telah ditolak.', variant: 'success');
        }
    }
}; ?>

<div class="space-y-6">
    <div>
        <flux:heading size="xl">Manajemen Reservasi</flux:heading>
        <flux:subheading>Setujui atau tolak pengajuan peminjaman ruangan dari mahasiswa/dosen.</flux:subheading>
    </div>

    <flux:separator variant="subtle" />

    <flux:table>
        <flux:columns>
            <flux:column>Pemohon</flux:column>
            <flux:column>Ruangan</flux:column>
            <flux:column>Kegiatan</flux:column>
            <flux:column>Waktu</flux:column>
            <flux:column>Status</flux:column>
            <flux:column>Aksi</flux:column>
        </flux:columns>

        <flux:rows>
            @forelse ($reservations as $reservation)
                <flux:row>
                    <flux:cell>
                        <div class="font-medium text-neutral-800">{{ $reservation->user->name }}</div>
                        <div class="text-xs text-neutral-500">{{ $reservation->user->email }}</div>
                    </flux:cell>
                    <flux:cell>
                        <div class="font-medium">{{ $reservation->room->name }}</div>
                        <div class="text-xs text-neutral-500">{{ $reservation->room->building }}</div>
                    </flux:cell>
                    <flux:cell>
                        <div class="max-w-[200px] truncate" title="{{ $reservation->description }}">
                            {{ $reservation->activity_name }}
                        </div>
                    </flux:cell>
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
                            <div class="flex gap-2">
                                <flux:button variant="ghost" size="sm" class="text-green-600 hover:text-green-700" wire:click="approve({{ $reservation->id }})" wire:confirm="Setujui reservasi ini?">
                                    Setujui
                                </flux:button>
                                <flux:button variant="ghost" size="sm" class="text-red-600 hover:text-red-700" wire:click="openRejectModal({{ $reservation->id }})">
                                    Tolak
                                </flux:button>
                            </div>
                        @else
                           <span class="text-xs text-neutral-400 italic">Selesai</span>
                        @endif
                    </flux:cell>
                </flux:row>
            @empty
                <flux:row>
                    <flux:cell colspan="6" class="text-center py-8 text-neutral-500">
                        Tidak ada pengajuan reservasi.
                    </flux:cell>
                </flux:row>
            @endforelse
        </flux:rows>
    </flux:table>

    <flux:modal name="reject-modal" class="md:w-[450px]">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">Tolak Reservasi</flux:heading>
                <flux:subheading>Berikan alasan penolakan agar pemohon dapat mengetahuinya.</flux:subheading>
            </div>

            <flux:field>
                <flux:label>Alasan Penolakan</flux:label>
                <flux:textarea wire:model="admin_note" placeholder="Contoh: Ruangan akan digunakan untuk perbaikan AC." />
            </flux:field>

            <div class="flex justify-end gap-2">
                <flux:modal.close>
                    <flux:button variant="ghost">Batal</flux:button>
                </flux:modal.close>
                <flux:button variant="danger" wire:click="reject">Tolak Sekarang</flux:button>
            </div>
        </div>
    </flux:modal>
</div>
