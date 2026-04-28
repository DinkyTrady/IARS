<?php

use App\Services\GeneticAlgorithmService;
use App\Models\AcademicSchedule;
use Livewire\Volt\Component;
use Flux\Flux;

new class extends Component {
    /**
     * Status untuk menampilkan loading state pada tombol generate.
     */
    public bool $isGenerating = false;

    /**
     * Mengambil data jadwal akademik terbaru yang sudah diurutkan berdasarkan hari dan jam.
     */
    public function with(): array
    {
        return [
            'schedules' => AcademicSchedule::with(['course', 'room'])
                ->orderBy('day')
                ->orderBy('start_time')
                ->get(),
        ];
    }

    /**
     * Menjalankan proses Genetic Algorithm untuk menyusun jadwal optimal.
     */
    public function generate(): void
    {
        $this->isGenerating = true;

        try {
            $service = new GeneticAlgorithmService();

            // Memanggil fungsi sesuai dengan struktur GeneticAlgorithmService yang Anda berikan
            $success = $service->generateOptimalSchedule();

            if ($success) {
                Flux::toast(
                    text: 'Jadwal akademik berhasil disusun secara optimal tanpa benturan.',
                    variant: 'success',
                );
            } else {
                Flux::toast(
                    text: 'Gagal menyusun jadwal. Pastikan data Mata Kuliah dan Ruangan sudah tersedia.',
                    variant: 'error',
                );
            }
        } catch (\Exception $e) {
            Flux::toast(
                text: 'Terjadi kesalahan sistem: ' . $e->getMessage(),
                variant: 'error',
            );
        } finally {
            $this->isGenerating = false;
        }
    }
}; ?>

<div class="space-y-6">
    <header class="flex flex-col sm:flex-row justify-between sm:items-end gap-4">
        <div>
            <flux:heading size="xl">Optimasi Jadwal Akademik</flux:heading>
            <flux:subheading>Gunakan Genetic Algorithm untuk menyusun jadwal kuliah otomatis dengan durasi SKS dinamis.
            </flux:subheading>
        </div>

        <flux:button variant="primary" wire:click="generate" :loading="$isGenerating" icon="cpu-chip">
            Generate Jadwal Baru
        </flux:button>
    </header>

    <flux:separator variant="subtle" />

    @if ($schedules->isEmpty())
        {{-- Tampilan saat data jadwal masih kosong --}}
        <div
            class="flex flex-col items-center justify-center py-20 bg-neutral-50 rounded-xl border border-dashed border-neutral-300">
            <flux:icon.calendar-days class="mb-4 text-neutral-400" size="xl" />
            <flux:heading>Belum Ada Jadwal</flux:heading>
            <flux:subheading>Klik tombol "Generate Jadwal Baru" untuk memulai optimasi ruangan dan waktu.</flux:subheading>
        </div>
    @else
        {{-- Tabel Hasil Generasi Jadwal --}}
        @php
            $daftarHari = [
                1 => 'Senin',
                2 => 'Selasa',
                3 => 'Rabu',
                4 => 'Kamis',
                5 => 'Jumat'
            ];
            // Kelompokkan jadwal berdasarkan hari
            $groupedSchedules = $schedules->groupBy('day');
        @endphp

        <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
            @for ($i = 1; $i <= 5; $i++)
                <div class="flex flex-col border border-neutral-200 rounded-lg overflow-hidden">
                    <div class="bg-neutral-100 px-4 py-2 font-semibold text-center border-b border-neutral-200">
                        {{ $daftarHari[$i] }}
                    </div>
                    <div class="p-4 flex-1 space-y-3 bg-white">
                        @if (isset($groupedSchedules[$i]))
                            @foreach ($groupedSchedules[$i] as $schedule)
                                <div class="p-3 border border-blue-100 bg-blue-50/50 rounded-md shadow-sm">
                                    <div class="text-xs font-semibold text-blue-600 mb-1">
                                        {{ substr($schedule->start_time, 0, 5) }} - {{ substr($schedule->end_time, 0, 5) }}
                                    </div>
                                    <div class="font-bold text-sm text-neutral-800 leading-tight mb-2">{{ $schedule->course->name }}</div>
                                    <div class="text-xs text-neutral-500 mt-1 flex justify-between items-center">
                                        <span>{{ $schedule->course->sks }} SKS</span>
                                        <flux:badge size="sm" variant="outline" color="blue">{{ $schedule->room->name }}</flux:badge>
                                    </div>
                                </div>
                            @endforeach
                        @else
                            <div class="text-center text-xs text-neutral-400 py-4 italic">Kosong</div>
                        @endif
                    </div>
                </div>
            @endfor
        </div>
    @endif
</div>