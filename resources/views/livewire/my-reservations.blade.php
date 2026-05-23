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
            'rejected' => Reservation::where('user_id', auth()->id())->where('status', 'rejected')->count(),
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

<div class="max-w-[1400px] mx-auto px-4 sm:px-6 space-y-6">
    <div class="flex flex-col sm:flex-row justify-between sm:items-end gap-4">
        <div>
            <h1 class="text-[32px] font-bold text-slate-900 tracking-[-0.02em] leading-tight">Riwayat Reservasi Saya</h1>
            <p class="text-[15px] text-slate-500 mt-1">Pantau dan kelola status pengajuan peminjaman ruangan Anda secara langsung.</p>
        </div>
    </div>
    
    {{-- Filter Toolbar --}}
    <div class="bg-white rounded-[24px] p-6 shadow-sm border border-slate-200 flex flex-col sm:flex-row items-center justify-between gap-4">
        <div class="text-[15px] font-semibold text-slate-700 flex items-center gap-2">
            <flux:icon.funnel class="size-5 text-slate-400" />
            Filter Status
        </div>
        <div class="w-full sm:w-64">
            <select wire:model.live="statusFilter" class="w-full h-12 rounded-[14px] border border-slate-200 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 text-sm font-medium text-slate-700 outline-none px-4 bg-white transition-all">
                <option value="all">Semua Status</option>
                <option value="pending">Menunggu (Pending)</option>
                <option value="approved">Disetujui (Approved)</option>
                <option value="rejected">Ditolak (Rejected)</option>
                <option value="canceled">Dibatalkan (Canceled)</option>
            </select>
        </div>
    </div>
    
    {{-- Compact Stats Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="h-[88px] bg-white rounded-[20px] shadow-sm border border-slate-100 flex items-center px-5 gap-4 transition-all duration-200 ease-in-out hover:-translate-y-[2px] group">
            <div class="size-12 rounded-full bg-blue-50 flex items-center justify-center shrink-0">
                <flux:icon.document-duplicate class="size-6 text-blue-600" />
            </div>
            <div>
                <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Total Pengajuan</div>
                <div class="text-2xl font-bold text-slate-900 tracking-tight leading-none mt-1">{{ $stats['total'] }}</div>
            </div>
        </div>

        <div class="h-[88px] bg-white rounded-[20px] shadow-sm border border-slate-100 flex items-center px-5 gap-4 transition-all duration-200 ease-in-out hover:-translate-y-[2px] group">
            <div class="size-12 rounded-full bg-green-50 flex items-center justify-center shrink-0">
                <flux:icon.check-circle class="size-6 text-green-600" />
            </div>
            <div>
                <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Disetujui</div>
                <div class="text-2xl font-bold text-slate-900 tracking-tight leading-none mt-1">{{ $stats['approved'] }}</div>
            </div>
        </div>

        <div class="h-[88px] bg-white rounded-[20px] shadow-sm border border-slate-100 flex items-center px-5 gap-4 transition-all duration-200 ease-in-out hover:-translate-y-[2px] group">
            <div class="size-12 rounded-full bg-amber-50 flex items-center justify-center shrink-0">
                <flux:icon.clock class="size-6 text-amber-600" />
            </div>
            <div>
                <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Menunggu</div>
                <div class="text-2xl font-bold text-slate-900 tracking-tight leading-none mt-1">{{ $stats['pending'] }}</div>
            </div>
        </div>

        <div class="h-[88px] bg-white rounded-[20px] shadow-sm border border-slate-100 flex items-center px-5 gap-4 transition-all duration-200 ease-in-out hover:-translate-y-[2px] group">
            <div class="size-12 rounded-full bg-red-50 flex items-center justify-center shrink-0">
                <flux:icon.x-circle class="size-6 text-red-600" />
            </div>
            <div>
                <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Ditolak</div>
                <div class="text-2xl font-bold text-slate-900 tracking-tight leading-none mt-1">{{ $stats['rejected'] }}</div>
            </div>
        </div>
    </div>

    {{-- Table Section --}}
    <div class="bg-white rounded-[20px] shadow-sm border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="bg-slate-50 border-b border-slate-100 text-[13px] font-semibold text-slate-500">
                    <tr>
                        <th class="px-6 py-4">Ruangan</th>
                        <th class="px-6 py-4">Kegiatan</th>
                        <th class="px-6 py-4">Waktu</th>
                        <th class="px-6 py-4">Status</th>
                        <th class="px-6 py-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($reservations as $reservation)
                        <tr wire:key="{{ $reservation->id }}" class="hover:bg-slate-50 transition duration-200 group">
                            <td class="px-6 py-4">
                                <div class="font-bold text-slate-900">{{ $reservation->room->name }}</div>
                                <div class="text-[13px] text-slate-500 mt-0.5">{{ $reservation->room->building }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="font-medium text-slate-900 max-w-[200px] truncate" title="{{ $reservation->activity_name }}">
                                    {{ $reservation->activity_name }}
                                </div>
                                @if($reservation->description)
                                    <div class="text-[13px] text-slate-500 mt-0.5 max-w-[200px] truncate" title="{{ $reservation->description }}">
                                        {{ $reservation->description }}
                                    </div>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <div class="font-medium text-slate-900">{{ $reservation->date->format('d M Y') }}</div>
                                <div class="text-[13px] text-slate-500 mt-0.5">
                                    {{ substr($reservation->start_time, 0, 5) }} - {{ substr($reservation->end_time, 0, 5) }}
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                @php
                                    $badgeClass = match ($reservation->status) {
                                        'approved' => 'bg-green-100 text-green-800',
                                        'pending' => 'bg-amber-100 text-amber-800',
                                        'rejected' => 'bg-red-100 text-red-800',
                                        'canceled' => 'bg-slate-100 text-slate-700',
                                        default => 'bg-slate-100 text-slate-700',
                                    };
                                @endphp
                                <span class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-semibold {{ $badgeClass }}">
                                    {{ ucfirst($reservation->status) }}
                                </span>

                                @if ($reservation->status === 'rejected' && $reservation->note)
                                    <div class="text-[11px] text-red-600 mt-1.5 font-medium flex items-start gap-1 max-w-[200px]">
                                        <flux:icon.exclamation-circle variant="mini" class="size-3.5 shrink-0 mt-0.5" />
                                        <span>{{ $reservation->note }}</span>
                                    </div>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right">
                                @if ($reservation->status === 'pending')
                                    <button 
                                        type="button"
                                        wire:click="cancel({{ $reservation->id }})"
                                        wire:confirm="Apakah Anda yakin ingin membatalkan reservasi ini?"
                                        class="inline-flex items-center justify-center bg-red-50 text-red-600 rounded-[10px] px-3 py-2 text-xs font-bold hover:bg-red-100 transition ease duration-200 outline-none"
                                    >
                                        Batalkan
                                    </button>
                                @else
                                    <span class="text-[13px] text-slate-400 font-medium">-</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-16 text-center">
                                <div class="flex flex-col items-center justify-center">
                                    <div class="size-16 rounded-full bg-slate-50 flex items-center justify-center mb-4 text-slate-400">
                                        <flux:icon.calendar-days size="xl" variant="outline" />
                                    </div>

                                    <h3 class="text-[15px] font-bold text-slate-900">Belum ada reservasi</h3>
                                    <p class="text-sm text-slate-500 mt-1 max-w-sm mx-auto">
                                        Anda belum memiliki riwayat reservasi pada kategori ini. Reservasi yang Anda buat akan muncul di sini.
                                    </p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($reservations->hasPages())
            <div class="px-6 py-4 border-t border-slate-100 bg-slate-50/50">
                {{ $reservations->links() }}
            </div>
        @endif
    </div>
</div>