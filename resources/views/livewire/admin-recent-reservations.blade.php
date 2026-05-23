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
            'recentReservations' => Reservation::with(['user', 'room'])
                ->latest()
                ->limit(10)
                ->get(),
            'pendingCount' => Reservation::where('status', 'pending')->count(),
        ];
    }

    public function approve(int $id): void
    {
        $reservation = Reservation::findOrFail($id);
        
        $service = new \App\Services\GeneticAlgorithmService();
        $canResolve = $service->resolveConflictForReservation($reservation);

        if (! $canResolve) {
            Flux::toast('Gagal menyetujui. Reservasi bentrok dan tidak ada slot kosong.', variant: 'error');
            return;
        }

        $reservation->update(['status' => 'approved']);
        Flux::toast('Reservasi disetujui.', variant: 'success');
    }

    public function openRejectModal(int $id): void
    {
        $this->selectedReservation = Reservation::findOrFail($id);
        $this->admin_note = '';
        Flux::modal('reject-modal-recent')->show();
    }

    public function reject(): void
    {
        $this->validate([
            'admin_note' => 'required|string|min:3'
        ], [
            'admin_note.required' => 'Alasan penolakan wajib diisi.'
        ]);

        if ($this->selectedReservation) {
            $this->selectedReservation->update([
                'status' => 'rejected',
                'note' => $this->admin_note,
            ]);

            Flux::modal('reject-modal-recent')->close();
            $this->reset(['selectedReservation', 'admin_note']);

            Flux::toast('Reservasi telah ditolak.', variant: 'success');
        }
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

    <div class="bg-white border border-blue-100 shadow-lg shadow-blue-900/5 rounded-2xl overflow-hidden transition-all duration-300 hover:shadow-xl hover:shadow-blue-900/10 hover:-translate-y-0.5 mt-4">
        <div class="bg-gradient-to-r from-blue-600 via-blue-500 to-violet-500 px-6 py-4 border-b border-blue-100 flex items-center justify-between">
            <h3 class="font-bold text-white text-lg flex items-center gap-2">
                <flux:icon.clock class="text-white/80" />
                Data Terbaru
            </h3>
        </div>
        <div class="p-2 sm:p-4 overflow-x-auto">
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
                <flux:table.row wire:key="{{ $reservation->id }}" class="transition-colors duration-200 hover:bg-blue-50/50">
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
                            <div class="flex gap-2">
                                <button 
                                    class="inline-flex items-center justify-center px-3 py-1.5 text-xs font-bold text-white transition-all duration-200 border border-transparent rounded-lg shadow-sm bg-emerald-600 hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-1"
                                    wire:click="approve({{ $reservation->id }})"
                                    wire:confirm="Setujui reservasi ini?">
                                    Setujui
                                </button>
                                <button 
                                    class="inline-flex items-center justify-center px-3 py-1.5 text-xs font-bold text-white transition-all duration-200 border border-transparent rounded-lg shadow-sm bg-rose-600 hover:bg-rose-700 focus:outline-none focus:ring-2 focus:ring-rose-500 focus:ring-offset-1"
                                    wire:click="openRejectModal({{ $reservation->id }})">
                                    Tolak
                                </button>
                            </div>
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
    </div>

    <flux:modal name="reject-modal-recent" class="md:w-[450px]">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">Tolak Reservasi</flux:heading>
                <flux:subheading>Berikan alasan penolakan agar pemohon dapat mengetahuinya.</flux:subheading>
            </div>

            <flux:field>
                <flux:label>Alasan Penolakan</flux:label>
                <flux:textarea wire:model="admin_note" placeholder="Contoh: Ruangan akan digunakan untuk perbaikan AC." />
                <flux:error name="admin_note" />
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
