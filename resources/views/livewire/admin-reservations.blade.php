<?php

use App\Models\Reservation;
use Livewire\Volt\Component;
use Livewire\WithPagination;
use Flux\Flux;

new class extends Component {
    use WithPagination; // Tambahkan paginasi untuk performa

    public ?Reservation $selectedReservation = null;
    public string $admin_note = '';

    // Properti untuk filter status reservasi
    public string $statusFilter = 'all';

    public function with(): array
    {
        $query = Reservation::with(['user', 'room'])->latest();

        // Logika filter status
        if ($this->statusFilter !== 'all') {
            $query->where('status', $this->statusFilter);
        }

        return [
            'reservations' => $query->paginate(10),
        ];
    }

    public function approve(int $id): void
    {
        $reservation = Reservation::findOrFail($id);

        $service = new \App\Services\GeneticAlgorithmService();
        $canResolve = $service->resolveConflictForReservation($reservation);

        if (! $canResolve) {
            Flux::toast('Gagal menyetujui. Reservasi bentrok dan tidak ada slot kosong untuk memindahkan jadwal kuliah yang tergusur.', variant: 'error');
            return;
        }

        $reservation->update(['status' => 'approved']);

        Flux::toast('Reservasi telah disetujui. Jadwal akademik disesuaikan otomatis jika ada bentrok.', variant: 'success');
    }

    public function openRejectModal(int $id): void
    {
        $this->selectedReservation = Reservation::findOrFail($id);
        $this->admin_note = '';
        Flux::modal('reject-modal')->show(); // Syntax Flux yang direkomendasikan
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

            Flux::modal('reject-modal')->close();
            $this->reset(['selectedReservation', 'admin_note']);

            Flux::toast('Reservasi telah ditolak.', variant: 'success');
        }
    }
}; ?>

<div class="space-y-6">
    <div class="flex flex-col sm:flex-row justify-between sm:items-end gap-4">
        <div>
            <flux:heading size="xl" class="text-transparent bg-clip-text bg-gradient-to-r from-blue-600 to-violet-600 font-bold">Persetujuan Reservasi</flux:heading>
            <flux:subheading>Setujui atau tolak pengajuan peminjaman ruangan dari mahasiswa/dosen.</flux:subheading>
        </div>

        {{-- Filter Status --}}
        <div class="w-full sm:w-48">
            <flux:select wire:model.live="statusFilter" size="sm" placeholder="Filter Status">
                <flux:select.option value="all">Semua Status</flux:select.option>
                <flux:select.option value="pending">Menunggu (Pending)</flux:select.option>
                <flux:select.option value="approved">Disetujui (Approved)</flux:select.option>
                <flux:select.option value="rejected">Ditolak (Rejected)</flux:select.option>
            </flux:select>
        </div>
    </div>

    <div class="bg-white border border-blue-100 shadow-lg shadow-blue-900/5 rounded-2xl overflow-hidden transition-all duration-300 hover:shadow-xl hover:shadow-blue-900/10 hover:-translate-y-0.5">
        <div class="bg-gradient-to-r from-blue-600 via-blue-500 to-violet-500 px-6 py-4 border-b border-blue-100 flex items-center justify-between">
            <h3 class="font-bold text-white text-lg flex items-center gap-2">
                <flux:icon.calendar class="text-white/80" />
                Daftar Reservasi
            </h3>
        </div>
        <div class="p-2 sm:p-4 overflow-x-auto">
            <flux:table>
        {{-- FIX ERROR: Ubah <flux:columns> menjadi <flux:table.columns> --}}
                <flux:table.columns>
                    <flux:table.column>Pemohon</flux:table.column>
                    <flux:table.column>Ruangan</flux:table.column>
                    <flux:table.column>Kegiatan</flux:table.column>
                    <flux:table.column>Waktu</flux:table.column>
                    <flux:table.column>Status</flux:table.column>
                    <flux:table.column>Aksi</flux:table.column>
                </flux:table.columns>

                {{-- FIX ERROR: Ubah <flux:rows> menjadi <flux:table.rows> --}}
                        <flux:table.rows>
                            @forelse ($reservations as $reservation)
                                <flux:table.row class="transition-colors duration-200 hover:bg-blue-50/50">
                                    <flux:table.cell>
                                        <div class="font-medium text-neutral-800">{{ $reservation->user->name }}</div>
                                        <div class="text-xs text-neutral-500">{{ $reservation->user->email }}</div>
                                    </flux:table.cell>
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
                                        <div class="text-sm">
                                            {{ \Carbon\Carbon::parse($reservation->date)->format('d M Y') }}</div>
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
                                        <flux:badge color="{{ $color }}" size="sm">{{ ucfirst($reservation->status) }}
                                        </flux:badge>
                                    </flux:table.cell>
                                    <flux:table.cell>
                                        @if ($reservation->status === 'pending')
                                            <div class="flex gap-2">
                                                <flux:button variant="ghost" size="sm"
                                                    class="text-green-600 hover:text-green-700"
                                                    wire:click="approve({{ $reservation->id }})"
                                                    wire:confirm="Setujui reservasi ini?">
                                                    Setujui
                                                </flux:button>
                                                <flux:button variant="ghost" size="sm" class="text-red-600 hover:text-red-700"
                                                    wire:click="openRejectModal({{ $reservation->id }})">
                                                    Tolak
                                                </flux:button>
                                            </div>
                                        @else
                                            <span class="text-xs text-neutral-400 italic">Selesai</span>
                                        @endif
                                    </flux:table.cell>
                                </flux:table.row>
                            @empty
                                <flux:table.row>
                                    <flux:table.cell colspan="6" class="text-center py-8 text-neutral-500">
                                        Tidak ada pengajuan reservasi.
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

    <flux:modal name="reject-modal" class="md:w-[450px]">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">Tolak Reservasi</flux:heading>
                <flux:subheading>Berikan alasan penolakan agar pemohon dapat mengetahuinya.</flux:subheading>
            </div>

            <flux:field>
                <flux:label>Alasan Penolakan</flux:label>
                <flux:textarea wire:model="admin_note"
                    placeholder="Contoh: Ruangan akan digunakan untuk perbaikan AC." />
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