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
            <h1 class="text-2xl font-extrabold text-zinc-900 tracking-tight">Jadwal Perkuliahan</h1>
            <p class="text-xs text-zinc-500 font-semibold mt-0.5">Lihat jadwal akademik resmi yang telah disusun oleh sistem.</p>
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
        <div class="flex flex-col items-center justify-center py-20 bg-zinc-50 rounded-2xl border-2 border-dashed border-zinc-200">
            <flux:icon.calendar-days class="mb-3 text-zinc-400 size-10" />
            <flux:heading class="font-extrabold text-zinc-800 text-sm">Belum Ada Jadwal</flux:heading>
            <flux:subheading class="text-xs text-zinc-500">Jadwal akademik belum tersedia. Hubungi admin untuk informasi lebih lanjut.</flux:subheading>
        </div>
    @else
        @php
            $daftarHari = [1 => 'Senin', 2 => 'Selasa', 3 => 'Rabu', 4 => 'Kamis', 5 => 'Jumat'];
            $groupedSchedules = $schedules->groupBy('day');
        @endphp

        <div class="space-y-6">
            @foreach ($groupedSchedules as $day => $daySchedules)
                <div>
                    <div class="flex items-center gap-2 mb-3">
                        <span class="w-2.5 h-4 bg-blue-600 rounded-sm inline-block"></span>
                        <h3 class="font-extrabold text-md text-zinc-800">{{ $daftarHari[$day] ?? '-' }}</h3>
                        <flux:badge size="sm" color="neutral" class="text-[10px] py-0.5 px-1.5 font-bold uppercase tracking-wide">{{ $daySchedules->count() }} kelas</flux:badge>
                    </div>

                    <div class="bg-white border border-zinc-200 shadow-sm rounded-2xl overflow-hidden">
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
                                        <flux:table.row wire:key="{{ $schedule->id }}" class="transition-colors duration-200 hover:bg-zinc-50/50">
                                            <flux:table.cell>
                                                <span class="font-mono text-xs font-bold text-zinc-700 bg-zinc-100 px-2 py-1 rounded-md border border-zinc-200 inline-block">
                                                    {{ substr($schedule->start_time, 0, 5) }} – {{ substr($schedule->end_time, 0, 5) }}
                                                </span>
                                            </flux:table.cell>
                                            <flux:table.cell>
                                                <div class="font-bold text-sm text-zinc-800">{{ $schedule->course->name }}</div>
                                                <div class="text-xs text-zinc-400 font-semibold">{{ $schedule->course->code }}</div>
                                            </flux:table.cell>
                                            <flux:table.cell>
                                                <flux:badge color="neutral" size="sm" class="text-[10px] font-bold">{{ $schedule->course->sks }} SKS</flux:badge>
                                            </flux:table.cell>
                                            <flux:table.cell class="text-sm font-semibold text-zinc-700">
                                                {{ $schedule->lecturer?->name ?? '-' }}
                                            </flux:table.cell>
                                            <flux:table.cell>
                                                <div class="text-sm font-bold text-zinc-800">{{ $schedule->room->name }}</div>
                                                <div class="text-xs text-zinc-400 font-semibold">{{ $schedule->room->building }}, Lt. {{ $schedule->room->floor }}</div>
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
