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
        <flux:table>
            <flux:table.columns>
                <flux:table.column>Hari</flux:table.column>
                <flux:table.column>Waktu</flux:table.column>
                <flux:table.column>Mata Kuliah</flux:table.column>
                <flux:table.column>Ruangan</flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @foreach ($schedules as $schedule)
                    <flux:table.row :key="$schedule->id">
                        <flux:table.cell>
                            @php
                                $daftarHari = [
                                    1 => 'Senin',
                                    2 => 'Selasa',
                                    3 => 'Rabu',
                                    4 => 'Kamis',
                                    5 => 'Jumat'
                                ];
                            @endphp
                            <span class="font-medium text-neutral-900">{{ $daftarHari[$schedule->day] ?? 'N/A' }}</span>
                        </flux:table.cell>

                        <flux:table.cell>
                            <span class="text-sm">
                                {{ substr($schedule->start_time, 0, 5) }} - {{ substr($schedule->end_time, 0, 5) }}
                            </span>
                        </flux:table.cell>

                        <flux:table.cell>
                            <div class="flex flex-col">
                                <span class="font-medium text-neutral-900">{{ $schedule->course->name }}</span>
                                <span class="text-xs text-neutral-500">
                                    {{ $schedule->course->code }} | {{ $schedule->course->sks }} SKS | Semester
                                    {{ $schedule->course->semester }}
                                </span>
                            </div>
                        </flux:table.cell>

                        <flux:table.cell>
                            <flux:badge size="sm" variant="outline" color="blue">
                                {{ $schedule->room->name }}
                            </flux:badge>
                        </flux:table.cell>
                    </flux:table.row>
                @endforeach
            </flux:table.rows>
        </flux:table>
    @endif
</div>