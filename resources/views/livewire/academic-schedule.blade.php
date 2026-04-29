<?php

use App\Models\AcademicSchedule;
use Livewire\Volt\Component;

new class extends Component {
    public int $selectedDay = 0; // 0 = semua hari

    public function with(): array
    {
        $query = AcademicSchedule::with(['course', 'room', 'lecturer'])
            ->orderBy('day')
            ->orderBy('start_time');

        if ($this->selectedDay > 0) {
            $query->where('day', $this->selectedDay);
        }

        return [
            'schedules' => $query->get(),
        ];
    }
}; ?>

<div class="space-y-6">
    <header class="flex flex-col sm:flex-row justify-between sm:items-end gap-4">
        <div>
            <flux:heading size="xl" class="text-transparent bg-clip-text bg-gradient-to-r from-blue-600 to-violet-600 font-bold">Jadwal Perkuliahan</flux:heading>
            <flux:subheading>Lihat jadwal akademik resmi yang telah disusun oleh sistem.</flux:subheading>
        </div>
        <flux:select wire:model.live="selectedDay" class="w-full sm:w-48" size="sm">
            <flux:select.option value="0">Semua Hari</flux:select.option>
            <flux:select.option value="1">Senin</flux:select.option>
            <flux:select.option value="2">Selasa</flux:select.option>
            <flux:select.option value="3">Rabu</flux:select.option>
            <flux:select.option value="4">Kamis</flux:select.option>
            <flux:select.option value="5">Jumat</flux:select.option>
        </flux:select>
    </header>

    <flux:separator variant="subtle" />

    @if ($schedules->isEmpty())
        <div class="flex flex-col items-center justify-center py-20 bg-neutral-50 rounded-xl border border-dashed border-neutral-300">
            <flux:icon.calendar-days class="mb-4 text-neutral-400" size="xl" />
            <flux:heading>Belum Ada Jadwal</flux:heading>
            <flux:subheading>Jadwal akademik belum tersedia. Hubungi admin untuk informasi lebih lanjut.</flux:subheading>
        </div>
    @else
        @php
            $daftarHari = [1 => 'Senin', 2 => 'Selasa', 3 => 'Rabu', 4 => 'Kamis', 5 => 'Jumat'];
            $groupedSchedules = $schedules->groupBy('day');
        @endphp

        <div class="space-y-6">
            @foreach ($groupedSchedules as $day => $daySchedules)
                <div>
                    <div class="flex items-center gap-3 mb-3">
                        <h3 class="font-semibold text-lg text-neutral-800">{{ $daftarHari[$day] ?? '-' }}</h3>
                        <flux:badge size="sm" color="blue">{{ $daySchedules->count() }} kelas</flux:badge>
                    </div>

                    <div class="bg-white border border-blue-100 shadow-lg shadow-blue-900/5 rounded-2xl overflow-hidden transition-all duration-300 hover:shadow-xl hover:shadow-blue-900/10 hover:-translate-y-0.5">
                        <div class="p-2 sm:p-4 overflow-x-auto">
                            <flux:table>
                        <flux:table.columns>
                            <flux:table.column>Waktu</flux:table.column>
                            <flux:table.column>Mata Kuliah</flux:table.column>
                            <flux:table.column>SKS</flux:table.column>
                            <flux:table.column>Dosen</flux:table.column>
                            <flux:table.column>Ruangan</flux:table.column>
                        </flux:table.columns>

                        <flux:table.rows>
                            @foreach ($daySchedules as $schedule)
                                <flux:table.row wire:key="{{ $schedule->id }}" class="transition-colors duration-200 hover:bg-blue-50/50">
                                    <flux:table.cell>
                                        <span class="font-mono text-sm font-medium text-blue-700">
                                            {{ substr($schedule->start_time, 0, 5) }} – {{ substr($schedule->end_time, 0, 5) }}
                                        </span>
                                    </flux:table.cell>
                                    <flux:table.cell>
                                        <div class="font-medium text-sm">{{ $schedule->course->name }}</div>
                                        <div class="text-xs text-neutral-400">{{ $schedule->course->code }}</div>
                                    </flux:table.cell>
                                    <flux:table.cell>
                                        <flux:badge color="violet" size="sm">{{ $schedule->course->sks }} SKS</flux:badge>
                                    </flux:table.cell>
                                    <flux:table.cell class="text-sm">
                                        {{ $schedule->lecturer?->name ?? '-' }}
                                    </flux:table.cell>
                                    <flux:table.cell>
                                        <div class="text-sm font-medium">{{ $schedule->room->name }}</div>
                                        <div class="text-xs text-neutral-400">{{ $schedule->room->building }}, Lt. {{ $schedule->room->floor }}</div>
                                    </flux:table.cell>
                                </flux:table.row>
                            @endforeach
                        </flux:table.rows>
                    </flux:table>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
