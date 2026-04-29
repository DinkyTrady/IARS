<?php

use App\Services\GeneticAlgorithmService;
use App\Models\AcademicSchedule;
use App\Models\Course;
use App\Models\Room;
use Livewire\Volt\Component;
use Flux\Flux;

new class extends Component {
    public bool $isGenerating = false;

    /**
     * Data requirements check sebelum generate.
     */
    public function getReadyToGenerateProperty(): bool
    {
        return Course::count() > 0 && Room::where('status', 'available')->count() > 0;
    }

    public function with(): array
    {
        return [
            'schedules' => AcademicSchedule::with(['course', 'room', 'lecturer'])
                ->orderBy('day')
                ->orderBy('start_time')
                ->get(),
            'coursesCount' => Course::count(),
            'availableRoomsCount' => Room::where('status', 'available')->count(),
        ];
    }

    /**
     * Menjalankan Genetic Algorithm untuk menyusun jadwal optimal.
     */
    public function generate(): void
    {
        if (! $this->readyToGenerate) {
            Flux::toast('Pastikan data Mata Kuliah dan Ruangan tersedia sebelum generate.', variant: 'error');
            return;
        }

        $this->isGenerating = true;

        try {
            $service = new GeneticAlgorithmService();
            $success = $service->generateOptimalSchedule();

            if ($success) {
                Flux::toast(
                    text: 'Jadwal akademik berhasil disusun secara optimal!',
                    variant: 'success',
                );
            } else {
                Flux::toast(
                    text: 'Gagal menyusun jadwal. Periksa kembali data yang tersedia.',
                    variant: 'error',
                );
            }
        } catch (\Exception $e) {
            Flux::toast(
                text: 'Terjadi kesalahan: ' . $e->getMessage(),
                variant: 'error',
            );
        } finally {
            $this->isGenerating = false;
        }
    }

    /**
     * Hapus semua jadwal yang sudah digenerate.
     */
    public function clearSchedules(): void
    {
        AcademicSchedule::truncate();
        Flux::toast('Jadwal berhasil dihapus.', variant: 'success');
    }
}; ?>

<div class="space-y-6">
    <header class="flex flex-col sm:flex-row justify-between sm:items-end gap-4">
        <div>
            <flux:heading size="xl">Optimasi Jadwal Akademik</flux:heading>
            <flux:subheading>Gunakan Genetic Algorithm untuk menyusun jadwal kuliah tanpa konflik ruangan dan dosen.
            </flux:subheading>
        </div>

        <div class="flex items-center gap-2">
            @if(!$schedules->isEmpty())
                <flux:button variant="ghost" size="sm" wire:click="clearSchedules"
                    wire:confirm="Yakin hapus semua jadwal yang sudah digenerate?"
                    icon="trash" class="text-red-600">
                    Hapus Jadwal
                </flux:button>
            @endif
            <flux:button variant="primary" wire:click="generate" :loading="$isGenerating" icon="cpu-chip"
                :disabled="!$readyToGenerate || $isGenerating">
                {{ $isGenerating ? 'Sedang Generate...' : 'Generate Jadwal Baru' }}
            </flux:button>
        </div>
    </header>

    {{-- Prerequisite Info --}}
    @if(!$readyToGenerate)
        <flux:callout variant="warning" icon="exclamation-triangle">
            <flux:callout.heading>Data Belum Lengkap</flux:callout.heading>
            <flux:callout.text>
                Untuk menjalankan GA, Anda membutuhkan minimal:
                <ul class="list-disc list-inside mt-1 space-y-1 text-sm">
                    <li class="{{ $coursesCount > 0 ? 'text-green-700' : 'text-red-700' }}">
                        {{ $coursesCount > 0 ? '✓' : '✗' }} Mata Kuliah ({{ $coursesCount }} tersedia)
                    </li>
                    <li class="{{ $availableRoomsCount > 0 ? 'text-green-700' : 'text-red-700' }}">
                        {{ $availableRoomsCount > 0 ? '✓' : '✗' }} Ruangan Tersedia ({{ $availableRoomsCount }} tersedia)
                    </li>
                </ul>
            </flux:callout.text>
        </flux:callout>
    @else
        <flux:callout variant="success" icon="information-circle">
            <flux:callout.text>
                Siap generate: <strong>{{ $coursesCount }} mata kuliah</strong> &amp;
                <strong>{{ $availableRoomsCount }} ruangan tersedia</strong>. Klik "Generate Jadwal Baru" untuk memulai.
            </flux:callout.text>
        </flux:callout>
    @endif

    <flux:separator variant="subtle" />

    @if ($schedules->isEmpty())
        <div class="flex flex-col items-center justify-center py-20 bg-neutral-50 rounded-xl border border-dashed border-neutral-300">
            <flux:icon.calendar-days class="mb-4 text-neutral-400" size="xl" />
            <flux:heading>Belum Ada Jadwal</flux:heading>
            <flux:subheading>Klik tombol "Generate Jadwal Baru" untuk memulai optimasi ruangan dan waktu.</flux:subheading>
        </div>
    @else
        {{-- Summary --}}
        <div class="flex items-center justify-between">
            <flux:text class="text-sm text-neutral-500">
                Total: <strong>{{ $schedules->count() }} sesi</strong> untuk {{ $schedules->groupBy('course_id')->count() }} mata kuliah
            </flux:text>
            <flux:badge variant="outline" color="blue" size="sm">
                Terakhir di-generate: {{ \Carbon\Carbon::parse($schedules->first()->created_at)->format('d M Y, H:i') }} WIB
            </flux:badge>
        </div>

        @php
            $daftarHari = [1 => 'Senin', 2 => 'Selasa', 3 => 'Rabu', 4 => 'Kamis', 5 => 'Jumat'];
            $groupedSchedules = $schedules->groupBy('day');
            $colors = ['blue', 'purple', 'green', 'orange', 'red'];
        @endphp

        <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
            @for ($i = 1; $i <= 5; $i++)
                @php $color = $colors[$i - 1]; @endphp
                <div class="flex flex-col border border-neutral-200 rounded-lg overflow-hidden">
                    <div class="px-4 py-2 font-semibold text-center text-sm border-b
                        @if($i === 1) bg-blue-50 text-blue-700 border-blue-200
                        @elseif($i === 2) bg-purple-50 text-purple-700 border-purple-200
                        @elseif($i === 3) bg-green-50 text-green-700 border-green-200
                        @elseif($i === 4) bg-orange-50 text-orange-700 border-orange-200
                        @else bg-red-50 text-red-700 border-red-200
                        @endif">
                        {{ $daftarHari[$i] }}
                        @isset($groupedSchedules[$i])
                            <span class="ml-1 text-xs opacity-70">({{ $groupedSchedules[$i]->count() }})</span>
                        @endisset
                    </div>
                    <div class="p-3 flex-1 space-y-2 bg-white min-h-[120px]">
                        @isset($groupedSchedules[$i])
                            @foreach ($groupedSchedules[$i] as $schedule)
                                <div class="p-2.5 border rounded-md shadow-sm
                                    @if($i === 1) border-blue-100 bg-blue-50/40
                                    @elseif($i === 2) border-purple-100 bg-purple-50/40
                                    @elseif($i === 3) border-green-100 bg-green-50/40
                                    @elseif($i === 4) border-orange-100 bg-orange-50/40
                                    @else border-red-100 bg-red-50/40
                                    @endif">
                                    <div class="text-[11px] font-semibold mb-1
                                        @if($i === 1) text-blue-600
                                        @elseif($i === 2) text-purple-600
                                        @elseif($i === 3) text-green-600
                                        @elseif($i === 4) text-orange-600
                                        @else text-red-600
                                        @endif">
                                        {{ substr($schedule->start_time, 0, 5) }} - {{ substr($schedule->end_time, 0, 5) }}
                                    </div>
                                    <div class="font-bold text-xs text-neutral-800 leading-tight mb-1.5">
                                        {{ $schedule->course->name }}
                                    </div>
                                    @if ($schedule->lecturer)
                                        <div class="text-[10px] text-neutral-500 mb-1 truncate" title="{{ $schedule->lecturer->name }}">
                                            {{ $schedule->lecturer->name }}
                                        </div>
                                    @endif
                                    <div class="flex justify-between items-center">
                                        <span class="text-[10px] text-neutral-400">{{ $schedule->course->sks }} SKS</span>
                                        <flux:badge size="sm" variant="outline"
                                            color="{{ $i === 1 ? 'blue' : ($i === 2 ? 'purple' : ($i === 3 ? 'green' : ($i === 4 ? 'orange' : 'red'))) }}">
                                            {{ $schedule->room->name }}
                                        </flux:badge>
                                    </div>
                                </div>
                            @endforeach
                        @else
                            <div class="flex items-center justify-center h-full text-xs text-neutral-300 italic py-6">
                                Kosong
                            </div>
                        @endisset
                    </div>
                </div>
            @endfor
        </div>
    @endif
</div>