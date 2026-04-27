<?php

namespace App\Services;

use App\Models\Course;
use App\Models\Room;
use App\Models\Lecturer;
use App\Models\AcademicSchedule; // <-- Tambahan untuk menyimpan ke DB
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB; // <-- Tambahan untuk eksekusi penyimpanan

class GeneticAlgorithmService
{
    private int $populationSize = 50;
    private int $maxGenerations = 100;
    private float $mutationRate = 0.1;

    // Standar durasi 1 SKS di Indonesia (dalam menit)
    private int $minutesPerSks = 50;

    // Slot jam mulai perkuliahan yang diperbolehkan di kampus (contoh)
    private array $allowedStartTimes = [
        '08:00',
        '09:00',
        '10:00',
        '11:00',
        '13:00',
        '14:00',
        '15:00'
    ];

    // MENGUBAH RETURN TYPE MENJADI BOOL (agar sesuai dengan Livewire yang mengecek sukses/gagal)
    public function generateOptimalSchedule(): bool
    {
        $population = $this->initializePopulation();

        for ($generation = 0; $generation < $this->maxGenerations; $generation++) {
            $fitnessScores = $this->calculateFitness($population);

            $bestScore = max($fitnessScores);
            // Jika tidak ada konflik sama sekali (Score = 1.0)
            if ($bestScore === 1.0) {
                $bestIndex = array_search($bestScore, $fitnessScores);

                // SIMPAN KE DATABASE SEBELUM RETURN
                $this->saveBestSchedule($population[$bestIndex]);
                return true;
            }

            $newPopulation = [];

            while (count($newPopulation) < $this->populationSize) {
                $parent1 = $this->selection($population, $fitnessScores);
                $parent2 = $this->selection($population, $fitnessScores);

                $offspring = $this->crossover($parent1, $parent2);
                $offspring = $this->mutate($offspring);

                $newPopulation[] = $offspring;
            }

            $population = $newPopulation;
        }

        $finalFitness = $this->calculateFitness($population);
        $bestIndex = array_search(max($finalFitness), $finalFitness);

        // SIMPAN KE DATABASE SEBELUM RETURN
        $this->saveBestSchedule($population[$bestIndex]);
        return true;
    }

    private function initializePopulation(): array
    {
        $population = [];
        $courses = Course::all();
        $rooms = Room::where('status', 'available')->get();
        // Asumsi perkuliahan: 1 (Senin) s.d 5 (Jumat)
        $days = [1, 2, 3, 4, 5];

        for ($i = 0; $i < $this->populationSize; $i++) {
            $chromosome = [];

            foreach ($courses as $course) {
                // 1. Pilih jam mulai acak dari slot yang diizinkan
                $randomStartTimeString = $this->allowedStartTimes[array_rand($this->allowedStartTimes)];

                // 2. Hitung durasi berdasarkan SKS Mata Kuliah (Dinamis)
                $durationMinutes = $course->sks * $this->minutesPerSks;

                // 3. Kalkulasi End Time menggunakan Carbon
                $startTime = Carbon::createFromFormat('H:i', $randomStartTimeString);
                $endTime = (clone $startTime)->addMinutes($durationMinutes);

                // Asumsi: jika tabel courses belum ada lecturer_id, bisa diambil random untuk testing
                // Pastikan disesuaikan dengan skema relasi database Anda.
                $lecturerId = $course->lecturer_id ?? Lecturer::inRandomOrder()->first()?->id ?? 1;

                $chromosome[] = [
                    'course_id' => $course->id,
                    'lecturer_id' => $lecturerId,
                    'room_id' => $rooms->random()->id,
                    'day' => $days[array_rand($days)],
                    'start_time' => $startTime->format('H:i:s'),
                    'end_time' => $endTime->format('H:i:s'),
                ];
            }
            $population[] = $chromosome;
        }

        return $population;
    }

    private function calculateFitness(array $population): array
    {
        $scores = [];

        foreach ($population as $chromosome) {
            $conflicts = 0;
            $totalGenes = count($chromosome);

            // Membandingkan setiap kelas dengan kelas lainnya dalam 1 jadwal (Kromosom)
            for ($i = 0; $i < $totalGenes; $i++) {
                for ($j = $i + 1; $j < $totalGenes; $j++) {
                    $geneA = $chromosome[$i];
                    $geneB = $chromosome[$j];

                    // Jika hari berbeda, dipastikan tidak ada konflik waktu
                    if ($geneA['day'] !== $geneB['day']) {
                        continue;
                    }

                    // Logika Irisan Waktu (Overlapping)
                    // Dua kegiatan beririsan JIKA (Start A < End B) DAN (End A > Start B)
                    $startA = strtotime($geneA['start_time']);
                    $endA = strtotime($geneA['end_time']);
                    $startB = strtotime($geneB['start_time']);
                    $endB = strtotime($geneB['end_time']);

                    if ($startA < $endB && $endA > $startB) {
                        // HUKUMAN 1: Ruangan sama dipakai 2 kelas bersamaan
                        if ($geneA['room_id'] === $geneB['room_id']) {
                            $conflicts++;
                        }
                        // HUKUMAN 2: Dosen sama mengajar 2 kelas berbeda bersamaan
                        if ($geneA['lecturer_id'] === $geneB['lecturer_id']) {
                            $conflicts++;
                        }
                    }
                }
            }

            // Fungsi fitness (Semakin sedikit konflik, nilai mendekati 1)
            $scores[] = 1 / (1 + $conflicts);
        }

        return $scores;
    }

    private function selection(array $population, array $fitnessScores): array
    {
        $tournamentSize = 3;
        $bestIndex = array_rand($population);

        for ($i = 0; $i < $tournamentSize - 1; $i++) {
            $randomIndex = array_rand($population);
            if ($fitnessScores[$randomIndex] > $fitnessScores[$bestIndex]) {
                $bestIndex = $randomIndex;
            }
        }

        return $population[$bestIndex];
    }

    private function crossover(array $parent1, array $parent2): array
    {
        $crossoverPoint = rand(1, count($parent1) - 1);

        return array_merge(
            array_slice($parent1, 0, $crossoverPoint),
            array_slice($parent2, $crossoverPoint)
        );
    }

    private function mutate(array $chromosome): array
    {
        $rooms = Room::where('status', 'available')->pluck('id')->toArray();
        $days = [1, 2, 3, 4, 5];

        foreach ($chromosome as &$gene) {
            if (rand(0, 100) / 100 < $this->mutationRate) {
                // Mutasi hari, ruangan, atau jam (termasuk rekalkulasi SKS)
                $gene['room_id'] = $rooms[array_rand($rooms)];
                $gene['day'] = $days[array_rand($days)];

                $course = Course::find($gene['course_id']);
                $newStartString = $this->allowedStartTimes[array_rand($this->allowedStartTimes)];

                $startTime = Carbon::createFromFormat('H:i', $newStartString);
                $endTime = (clone $startTime)->addMinutes($course->sks * $this->minutesPerSks);

                $gene['start_time'] = $startTime->format('H:i:s');
                $gene['end_time'] = $endTime->format('H:i:s');
            }
        }

        return $chromosome;
    }

    // --- FUNGSI BARU UNTUK MENYIMPAN KE DATABASE ---
    private function saveBestSchedule(array $schedule): void
    {
        DB::transaction(function () use ($schedule) {
            AcademicSchedule::truncate(); // Bersihkan jadwal lama
            foreach ($schedule as $data) {
                AcademicSchedule::create([
                    'course_id' => $data['course_id'],
                    'room_id' => $data['room_id'],
                    'day' => $data['day'],
                    'start_time' => $data['start_time'],
                    'end_time' => $data['end_time'],
                ]);
            }
        });
    }
}