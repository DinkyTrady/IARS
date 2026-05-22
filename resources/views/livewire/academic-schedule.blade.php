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

        $schedules = $query->get();

        return [
            'schedules' => $schedules,
            'totalClasses' => $schedules->count(),
            'totalSKS' => $schedules->sum(fn ($schedule) => (int) $schedule->course->sks),
        ];
    }
}; ?>

<div class="max-w-[1400px] mx-auto px-4 sm:px-6 space-y-6">
    {{-- Header & Filters --}}
    <div class="flex flex-col sm:flex-row justify-between sm:items-end gap-6 bg-white rounded-[24px] p-6 shadow-sm border border-slate-200 transition-all duration-300 hover:shadow-xl hover:shadow-blue-900/10 hover:-translate-y-1">
        <div>
            <h1 class="text-[32px] font-bold text-slate-900 tracking-[-0.02em] leading-tight">Jadwal Perkuliahan</h1>
            <p class="text-[15px] text-slate-500 mt-1">Lihat jadwal akademik resmi yang telah disusun oleh sistem.</p>
            
            <div class="mt-4 flex items-center gap-4">
                <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-blue-50 border border-blue-100">
                    <div class="size-2 rounded-full bg-blue-500"></div>
                    <span class="text-[13px] font-bold text-blue-700">{{ $totalClasses }} Kelas Terjadwal</span>
                </div>
                <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-purple-50 border border-purple-100">
                    <div class="size-2 rounded-full bg-purple-500"></div>
                    <span class="text-[13px] font-bold text-purple-700">Total {{ $totalSKS }} SKS</span>
                </div>
            </div>
        </div>
        
        <div class="w-full sm:w-64 space-y-2">
            <label class="block text-[13px] font-semibold text-slate-700">Filter Hari</label>
            <select wire:model.live="selectedDay" class="w-full h-12 rounded-[14px] border border-slate-200 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 text-sm font-medium text-slate-700 outline-none px-4 bg-white transition-all">
                <option value="0">Semua Hari</option>
                <option value="1">Senin</option>
                <option value="2">Selasa</option>
                <option value="3">Rabu</option>
                <option value="4">Kamis</option>
                <option value="5">Jumat</option>
            </select>
        </div>
    </div>

    @if ($schedules->isEmpty())
        <div class="bg-white rounded-[24px] p-16 shadow-sm border border-slate-200 flex flex-col items-center justify-center text-center">
            <div class="size-20 rounded-full bg-slate-50 flex items-center justify-center mb-5 text-4xl">
                🗓️
            </div>
            <h3 class="text-[18px] font-bold text-slate-900">Belum Ada Jadwal</h3>
            <p class="text-[15px] text-slate-500 mt-2 max-w-md mx-auto">
                Jadwal akademik belum tersedia untuk filter ini. Hubungi admin untuk informasi lebih lanjut.
            </p>
        </div>
    @else
        @php
            $daftarHari = [1 => 'Senin', 2 => 'Selasa', 3 => 'Rabu', 4 => 'Kamis', 5 => 'Jumat'];
            $groupedSchedules = $schedules->groupBy('day');
        @endphp

        <div class="space-y-8">
            @foreach ($groupedSchedules as $day => $daySchedules)
                <div>
                    {{-- Day Header Polish --}}
                    <div class="flex items-center justify-between bg-white rounded-[16px] px-5 py-3 shadow-sm border border-slate-200 mb-4">
                        <div class="flex items-center gap-3">
                            <div class="size-8 rounded-full bg-blue-50 flex items-center justify-center">
                                <flux:icon.calendar-days class="size-4 text-blue-600" />
                            </div>
                            <h3 class="font-bold text-[15px] text-slate-900 tracking-tight">{{ $daftarHari[$day] ?? '-' }}</h3>
                        </div>
                        <span class="inline-flex items-center px-3 py-1 rounded-full bg-slate-800 text-white shadow-sm text-[11px] font-bold uppercase tracking-widest">
                            {{ $daySchedules->count() }} Kelas
                        </span>
                    </div>

                    <div class="bg-white rounded-[20px] shadow-sm border border-slate-200 overflow-hidden transition-all duration-300 hover:shadow-xl hover:shadow-blue-900/10 hover:-translate-y-1">
                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-sm text-slate-600">
                                <thead class="bg-slate-50/80 border-b border-slate-200 text-[12px] font-extrabold text-slate-800 uppercase tracking-widest">
                                    <tr>
                                        <th class="px-6 py-4">Waktu</th>
                                        <th class="px-6 py-4">Mata Kuliah</th>
                                        <th class="px-6 py-4">SKS</th>
                                        <th class="px-6 py-4">Dosen</th>
                                        <th class="px-6 py-4">Ruangan</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    @foreach ($daySchedules as $schedule)
                                        <tr wire:key="{{ $schedule->id }}" class="hover:bg-slate-50 transition duration-200 group">
                                            <td class="px-6 py-4">
                                                <span class="inline-flex items-center font-mono text-[11px] font-bold text-slate-700 bg-slate-100 px-2.5 py-1 rounded-md border border-slate-200">
                                                    <flux:icon.clock class="size-3 mr-1.5 text-slate-400" />
                                                    {{ substr($schedule->start_time, 0, 5) }} – {{ substr($schedule->end_time, 0, 5) }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4">
                                                <div class="font-bold text-sm text-slate-900">{{ $schedule->course->name }}</div>
                                                <div class="text-[13px] text-slate-500 font-semibold mt-0.5">{{ $schedule->course->code }}</div>
                                            </td>
                                            <td class="px-6 py-4">
                                                @php
                                                    $sks = (int) $schedule->course->sks;
                                                    $sksColor = match ($sks) {
                                                        2 => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                                        3 => 'bg-indigo-50 text-indigo-700 border-indigo-200',
                                                        4 => 'bg-rose-50 text-rose-700 border-rose-200',
                                                        default => 'bg-slate-50 text-slate-700 border-slate-200',
                                                    };
                                                @endphp
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-[6px] border {{ $sksColor }} text-[11px] font-bold">
                                                    {{ $sks }} SKS
                                                </span>
                                            </td>
                                            <td class="px-6 py-4">
                                                <div class="text-[13px] font-semibold text-slate-700">
                                                    {{ $schedule->lecturer?->name ?? '-' }}
                                                </div>
                                            </td>
                                            <td class="px-6 py-4">
                                                <div class="text-[13px] font-bold text-slate-900">{{ $schedule->room->name }}</div>
                                                <div class="text-[12px] text-slate-500 font-semibold mt-0.5">{{ $schedule->room->building }}, Lt. {{ $schedule->room->floor }}</div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
