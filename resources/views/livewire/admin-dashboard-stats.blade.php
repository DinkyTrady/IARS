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

<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-4">
    {{-- Card 1: Total Ruangan --}}
    <flux:card class="flex flex-col gap-1 p-4 !border-none bg-gradient-to-br from-blue-500 to-blue-700 text-white shadow-md hover:shadow-lg hover:shadow-blue-500/30 transition-all duration-300 hover:-translate-y-1">
        <div class="flex items-center gap-2 text-sm text-blue-100 font-medium">
            <flux:icon.building-office-2 variant="mini" class="size-5" />
            <span>Total Ruangan</span>
        </div>
        <div class="text-3xl font-bold mt-1">{{ $totalRooms }}</div>
        <div class="text-xs text-blue-200 mt-auto">{{ $availableRooms }} tersedia</div>
    </flux:card>

    {{-- Card 2: Perlu Diproses --}}
    <flux:card class="flex flex-col gap-1 p-4 !border-none bg-gradient-to-br from-amber-500 to-orange-600 text-white shadow-md hover:shadow-lg hover:shadow-orange-500/30 transition-all duration-300 hover:-translate-y-1">
        <div class="flex items-center gap-2 text-sm text-amber-100 font-medium">
            <flux:icon.clock variant="mini" class="size-5" />
            <span>Perlu Diproses</span>
        </div>
        <div class="text-3xl font-bold mt-1">
            {{ $pendingReservations }}
        </div>
        <div class="text-xs text-amber-200 mt-auto">reservasi pending</div>
    </flux:card>

    {{-- Card 3: Disetujui Hari Ini --}}
    <flux:card class="flex flex-col gap-1 p-4 !border-none bg-gradient-to-br from-emerald-500 to-teal-600 text-white shadow-md hover:shadow-lg hover:shadow-emerald-500/30 transition-all duration-300 hover:-translate-y-1">
        <div class="flex items-center gap-2 text-sm text-emerald-100 font-medium">
            <flux:icon.calendar-days variant="mini" class="size-5" />
            <span>Disetujui Hari Ini</span>
        </div>
        <div class="text-3xl font-bold mt-1">{{ $approvedToday }}</div>
        <div class="text-xs text-emerald-200 mt-auto">reservasi aktif hari ini</div>
    </flux:card>

    {{-- Card 4: Total Pengguna --}}
    <flux:card class="flex flex-col gap-1 p-4 !border-none bg-gradient-to-br from-purple-500 to-fuchsia-600 text-white shadow-md hover:shadow-lg hover:shadow-purple-500/30 transition-all duration-300 hover:-translate-y-1">
        <div class="flex items-center gap-2 text-sm text-purple-100 font-medium">
            <flux:icon.users variant="mini" class="size-5" />
            <span>Total Pengguna</span>
        </div>
        <div class="text-3xl font-bold mt-1">{{ $totalUsers }}</div>
        <div class="text-xs text-purple-200 mt-auto">mahasiswa & dosen</div>
    </flux:card>

    {{-- Card 5: Jadwal Akademik --}}
    <flux:card class="flex flex-col gap-1 p-4 !border-none bg-gradient-to-br from-indigo-500 to-violet-600 text-white shadow-md hover:shadow-lg hover:shadow-indigo-500/30 transition-all duration-300 hover:-translate-y-1">
        <div class="flex items-center gap-2 text-sm text-indigo-100 font-medium">
            <flux:icon.academic-cap variant="mini" class="size-5" />
            <span>Jadwal Akademik</span>
        </div>
        <div class="text-3xl font-bold mt-1">{{ $totalSchedules }}</div>
        <div class="text-xs text-indigo-200 mt-auto">hasil generate GA</div>
    </flux:card>

    {{-- Card 6: Status Sistem --}}
    <flux:card class="flex flex-col gap-1 p-4 !border-none bg-gradient-to-br from-cyan-500 to-blue-600 text-white shadow-md hover:shadow-lg hover:shadow-cyan-500/30 transition-all duration-300 hover:-translate-y-1">
        <div class="flex items-center gap-2 text-sm text-cyan-100 font-medium">
            <flux:icon.check-circle variant="mini" class="size-5" />
            <span>Status Sistem</span>
        </div>
        <div class="text-3xl font-bold mt-1">Aktif</div>
        <div class="text-xs text-cyan-200 mt-auto">semua layanan berjalan</div>
    </flux:card>
</div>
