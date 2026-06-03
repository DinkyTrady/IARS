<?php

use App\Models\Reservation;
use Livewire\Volt\Component;

new class extends Component {
    public function with(): array
    {
        $userId = auth()->id();
        return [
            'totalReservations' => Reservation::where('user_id', $userId)->count(),
            'pendingReservations' => Reservation::where('user_id', $userId)->where('status', 'pending')->count(),
            'approvedReservations' => Reservation::where('user_id', $userId)->where('status', 'approved')->count(),
            'rejectedReservations' => Reservation::where('user_id', $userId)->whereIn('status', ['rejected', 'canceled'])->count(),
        ];
    }
}; ?>

<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    {{-- Card 1: Total Pengajuan --}}
    <flux:card class="flex flex-col gap-1 p-4 !border-none bg-gradient-to-br from-blue-500 to-blue-700 text-white shadow-md hover:shadow-lg hover:shadow-blue-500/30 transition-all duration-300 hover:-translate-y-1">
        <div class="flex items-center gap-2 text-sm text-blue-100 font-medium">
            <flux:icon.document-text variant="mini" class="size-5" />
            <span>Total Pengajuan</span>
        </div>
        <div class="text-3xl font-bold mt-1">{{ $totalReservations }}</div>
        <div class="text-xs text-blue-200 mt-auto">semua riwayat reservasi</div>
    </flux:card>

    {{-- Card 2: Menunggu Persetujuan --}}
    <flux:card class="flex flex-col gap-1 p-4 !border-none bg-gradient-to-br from-amber-500 to-orange-600 text-white shadow-md hover:shadow-lg hover:shadow-orange-500/30 transition-all duration-300 hover:-translate-y-1">
        <div class="flex items-center gap-2 text-sm text-amber-100 font-medium">
            <flux:icon.clock variant="mini" class="size-5" />
            <span>Menunggu Persetujuan</span>
        </div>
        <div class="text-3xl font-bold mt-1">{{ $pendingReservations }}</div>
        <div class="text-xs text-amber-200 mt-auto">sedang diproses admin</div>
    </flux:card>

    {{-- Card 3: Disetujui --}}
    <flux:card class="flex flex-col gap-1 p-4 !border-none bg-gradient-to-br from-emerald-500 to-teal-600 text-white shadow-md hover:shadow-lg hover:shadow-emerald-500/30 transition-all duration-300 hover:-translate-y-1">
        <div class="flex items-center gap-2 text-sm text-emerald-100 font-medium">
            <flux:icon.check-badge variant="mini" class="size-5" />
            <span>Reservasi Disetujui</span>
        </div>
        <div class="text-3xl font-bold mt-1">{{ $approvedReservations }}</div>
        <div class="text-xs text-emerald-200 mt-auto">siap digunakan</div>
    </flux:card>

    {{-- Card 4: Ditolak / Dibatalkan --}}
    <flux:card class="flex flex-col gap-1 p-4 !border-none bg-gradient-to-br from-rose-500 to-red-600 text-white shadow-md hover:shadow-lg hover:shadow-rose-500/30 transition-all duration-300 hover:-translate-y-1">
        <div class="flex items-center gap-2 text-sm text-rose-100 font-medium">
            <flux:icon.x-circle variant="mini" class="size-5" />
            <span>Ditolak / Batal</span>
        </div>
        <div class="text-3xl font-bold mt-1">{{ $rejectedReservations }}</div>
        <div class="text-xs text-rose-200 mt-auto">reservasi tidak valid</div>
    </flux:card>
</div>