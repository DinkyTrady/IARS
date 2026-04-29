<?php

use App\Models\Reservation;
use App\Models\Room;
use App\Models\User;
use App\Models\AcademicSchedule;
use Livewire\Volt\Component;

new class extends Component {
    public function with(): array
    {
        return [
            'totalRooms' => Room::count(),
            'availableRooms' => Room::where('status', 'available')->count(),
            'pendingReservations' => Reservation::where('status', 'pending')->count(),
            'approvedToday' => Reservation::where('status', 'approved')->whereDate('date', today())->count(),
            'totalUsers' => User::where('role', 'user')->count(),
            'totalSchedules' => AcademicSchedule::count(),
        ];
    }
}; ?>

<div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
    <flux:card class="flex flex-col gap-1 p-4">
        <div class="flex items-center gap-2 text-sm text-neutral-500">
            <flux:icon.building-office-2 variant="mini" class="size-4" />
            <span>Total Ruangan</span>
        </div>
        <div class="text-2xl font-bold text-neutral-800">{{ $totalRooms }}</div>
        <div class="text-xs text-green-600">{{ $availableRooms }} tersedia</div>
    </flux:card>

    <flux:card class="flex flex-col gap-1 p-4">
        <div class="flex items-center gap-2 text-sm text-neutral-500">
            <flux:icon.clock variant="mini" class="size-4" />
            <span>Perlu Diproses</span>
        </div>
        <div class="text-2xl font-bold {{ $pendingReservations > 0 ? 'text-yellow-600' : 'text-neutral-800' }}">
            {{ $pendingReservations }}
        </div>
        <div class="text-xs text-neutral-400">reservasi pending</div>
    </flux:card>

    <flux:card class="flex flex-col gap-1 p-4">
        <div class="flex items-center gap-2 text-sm text-neutral-500">
            <flux:icon.calendar-days variant="mini" class="size-4" />
            <span>Disetujui Hari Ini</span>
        </div>
        <div class="text-2xl font-bold text-green-600">{{ $approvedToday }}</div>
        <div class="text-xs text-neutral-400">reservasi aktif hari ini</div>
    </flux:card>

    <flux:card class="flex flex-col gap-1 p-4">
        <div class="flex items-center gap-2 text-sm text-neutral-500">
            <flux:icon.users variant="mini" class="size-4" />
            <span>Total Pengguna</span>
        </div>
        <div class="text-2xl font-bold text-neutral-800">{{ $totalUsers }}</div>
        <div class="text-xs text-neutral-400">mahasiswa & dosen</div>
    </flux:card>

    <flux:card class="flex flex-col gap-1 p-4">
        <div class="flex items-center gap-2 text-sm text-neutral-500">
            <flux:icon.academic-cap variant="mini" class="size-4" />
            <span>Jadwal Akademik</span>
        </div>
        <div class="text-2xl font-bold text-neutral-800">{{ $totalSchedules }}</div>
        <div class="text-xs text-neutral-400">hasil generate GA</div>
    </flux:card>

    <flux:card class="flex flex-col gap-1 p-4 bg-blue-50 border-blue-200">
        <div class="flex items-center gap-2 text-sm text-blue-600">
            <flux:icon.check-circle variant="mini" class="size-4" />
            <span>Status Sistem</span>
        </div>
        <div class="text-2xl font-bold text-blue-700">Aktif</div>
        <div class="text-xs text-blue-500">semua layanan berjalan</div>
    </flux:card>
</div>
