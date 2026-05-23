<?php

namespace App\Services;

use App\Models\AcademicSchedule;
use App\Models\Course;
use App\Models\Lecturer;
use App\Models\Reservation;
use App\Models\Room;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class GeneticAlgorithmService
{
    private int $populationSize = 50;

    private int $maxGenerations = 100;

    private float $mutationRate = 0.1;

    private int $minutesPerSks = 50;

    private array $allowedStartTimes = ['08:00', '09:00', '10:00', '11:00', '13:00', '14:00', '15:00'];

    private $courses;

    private $rooms;

    public function __construct()
    {
        $this->courses = Course::all()->keyBy('id');
        $this->rooms = Room::where('status', 'available')->get()->keyBy('id');
    }

    public function generateOptimalSchedule(): bool
    {
        $population = $this->initializePopulation();

        for ($generation = 0; $generation < $this->maxGenerations; $generation++) {
            $fitnessScores = $this->calculateFitness($population);

            $bestScore = max($fitnessScores);
            // Nilai maksimum untuk skor tanpa penalti adalah 1 (karena 1 / (1 + 0))
            if ($bestScore === 1.0) {
                $bestIndex = array_search($bestScore, $fitnessScores);
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
        $this->saveBestSchedule($population[$bestIndex]);

        return true;
    }

    private function initializePopulation(): array
    {
        $population = [];
        $days = [1, 2, 3, 4, 5];
        $defaultLecturerId = Lecturer::inRandomOrder()->first()?->id ?? 1;

        for ($i = 0; $i < $this->populationSize; $i++) {
            $chromosome = [];
            foreach ($this->courses as $course) {
                $randomStartTimeString = $this->allowedStartTimes[array_rand($this->allowedStartTimes)];
                $durationMinutes = $course->sks * $this->minutesPerSks;
                $startTime = Carbon::createFromFormat('H:i', $randomStartTimeString);
                $endTime = (clone $startTime)->addMinutes($durationMinutes);

                // Gunakan lecturer_id yang sudah di-assign ke course, atau fallback ke random
                $lecturerId = $course->lecturer_id ?? $defaultLecturerId;

                $chromosome[] = [
                    'course_id' => $course->id,
                    'lecturer_id' => $lecturerId,
                    'room_id' => $this->rooms->random()->id,
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
            $penalty = 0;
            $totalGenes = count($chromosome);

            for ($i = 0; $i < $totalGenes; $i++) {
                $geneA = $chromosome[$i];
                $courseA = $this->courses[$geneA['course_id']];
                $roomA = $this->rooms[$geneA['room_id']];

                // 1. HARD CONSTRAINT: Kapasitas Ruangan (-100)
                if ($courseA->expected_students > $roomA->capacity) {
                    $penalty += 100;
                }

                // 2. SOFT CONSTRAINT: Mengajar di Siang/Sore (-5)
                // Jika mulai di atas atau jam 13:00
                $startHour = (int) substr($geneA['start_time'], 0, 2);
                if ($startHour >= 13) {
                    $penalty += 5;
                }

                for ($j = $i + 1; $j < $totalGenes; $j++) {
                    $geneB = $chromosome[$j];

                    if ($geneA['day'] !== $geneB['day']) {
                        continue;
                    }

                    $startA = strtotime($geneA['start_time']);
                    $endA = strtotime($geneA['end_time']);
                    $startB = strtotime($geneB['start_time']);
                    $endB = strtotime($geneB['end_time']);

                    if ($startA < $endB && $endA > $startB) {
                        // HARD CONSTRAINT: Ruangan sama bentrok (-100)
                        if ($geneA['room_id'] === $geneB['room_id']) {
                            $penalty += 100;
                        }
                        // HARD CONSTRAINT: Dosen sama bentrok (-100)
                        if ($geneA['lecturer_id'] === $geneB['lecturer_id']) {
                            $penalty += 100;
                        }
                    }
                }
            }
            // Fitness score
            $scores[] = 1 / (1 + $penalty);
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
        $days = [1, 2, 3, 4, 5];
        $roomIds = $this->rooms->keys()->toArray();

        foreach ($chromosome as &$gene) {
            if (rand(0, 100) / 100 < $this->mutationRate) {
                $gene['room_id'] = $roomIds[array_rand($roomIds)];
                $gene['day'] = $days[array_rand($days)];

                $course = $this->courses[$gene['course_id']];
                $newStartString = $this->allowedStartTimes[array_rand($this->allowedStartTimes)];

                $startTime = Carbon::createFromFormat('H:i', $newStartString);
                $endTime = (clone $startTime)->addMinutes($course->sks * $this->minutesPerSks);

                $gene['start_time'] = $startTime->format('H:i:s');
                $gene['end_time'] = $endTime->format('H:i:s');
            }
        }

        return $chromosome;
    }

    private function saveBestSchedule(array $schedule): void
    {
        DB::transaction(function () use ($schedule) {
            AcademicSchedule::truncate();
            foreach ($schedule as $data) {
                AcademicSchedule::create([
                    'course_id' => $data['course_id'],
                    'lecturer_id' => $data['lecturer_id'],
                    'room_id' => $data['room_id'],
                    'day' => $data['day'],
                    'start_time' => $data['start_time'],
                    'end_time' => $data['end_time'],
                ]);
            }
        });
    }

    /**
     * Partial GA: Mencoba memindahkan jadwal akademik yang bentrok dengan reservasi
     * tanpa merubah seluruh jadwal lainnya.
     *
     * OPTIMASI v2:
     * - Hanya mutasi gen yang bentrok (lebih cepat)
     * - Early termination dengan threshold yang lebih realistis
     * - Penalty yang lebih seimbang untuk konflik dengan reservasi target
     * - Fallback mechanism: terima solusi terbaik jika tidak ada konflik kritis
     */
    public function resolveConflictForReservation(Reservation $reservation): bool
    {
        $dayOfWeek = Carbon::parse($reservation->date)->dayOfWeekIso;
        $start = strtotime($reservation->start_time);
        $end = strtotime($reservation->end_time);

        // 1. Cari jadwal akademik yang bentrok dengan reservasi ini
        $conflicts = AcademicSchedule::where('room_id', $reservation->room_id)
            ->where('day', $dayOfWeek)
            ->get()
            ->filter(function ($schedule) use ($start, $end) {
                $sStart = strtotime($schedule->start_time);
                $sEnd = strtotime($schedule->end_time);

                return $sStart < $end && $sEnd > $start;
            });

        if ($conflicts->isEmpty()) {
            return true; // Tidak ada jadwal yang tergusur
        }

        $conflictCourseIds = $conflicts->pluck('course_id')->toArray();

        // 2. Siapkan chromosome awal (kunci semua jadwal kecuali yang bentrok)
        $currentSchedules = AcademicSchedule::all();
        $baseChromosome = [];
        foreach ($currentSchedules as $schedule) {
            $baseChromosome[] = [
                'course_id' => $schedule->course_id,
                'lecturer_id' => $schedule->lecturer_id,
                'room_id' => $schedule->room_id,
                'day' => $schedule->day,
                'start_time' => $schedule->start_time,
                'end_time' => $schedule->end_time,
            ];
        }

        $days = [1, 2, 3, 4, 5];
        $roomIds = $this->rooms->keys()->toArray();
        $population = [];

        // 3. Inisialisasi populasi dengan variasi random untuk gen yang bentrok
        for ($i = 0; $i < $this->populationSize; $i++) {
            $chromosome = $baseChromosome;
            foreach ($chromosome as &$gene) {
                if (in_array($gene['course_id'], $conflictCourseIds)) {
                    // Randomize untuk mencari slot alternatif
                    $gene['room_id'] = $roomIds[array_rand($roomIds)];
                    $gene['day'] = $days[array_rand($days)];

                    $course = $this->courses[$gene['course_id']];
                    $newStartString = $this->allowedStartTimes[array_rand($this->allowedStartTimes)];
                    $startTime = Carbon::createFromFormat('H:i', $newStartString);
                    $endTime = (clone $startTime)->addMinutes($course->sks * $this->minutesPerSks);

                    $gene['start_time'] = $startTime->format('H:i:s');
                    $gene['end_time'] = $endTime->format('H:i:s');
                }
            }
            $population[] = $chromosome;
        }

        // 4. Evolusi Partial dengan optimasi
        $bestOverallScore = 0;
        $bestOverallChromosome = null;

        for ($generation = 0; $generation < $this->maxGenerations; $generation++) {
            $fitnessScores = [];

            foreach ($population as $chromosome) {
                $penalty = 0;
                $criticalPenalty = 0; // Track penalty kritis terpisah
                $totalGenes = count($chromosome);

                for ($i = 0; $i < $totalGenes; $i++) {
                    $geneA = $chromosome[$i];
                    $courseA = $this->courses[$geneA['course_id']];
                    $roomA = $this->rooms[$geneA['room_id']];

                    // HARD CONSTRAINT: Kapasitas ruangan
                    if ($courseA->expected_students > $roomA->capacity) {
                        $penalty += 100;
                    }

                    // SOFT CONSTRAINT: Jam siang/sore
                    $startHour = (int) substr($geneA['start_time'], 0, 2);
                    if ($startHour >= 13) {
                        $penalty += 5;
                    }

                    // CRITICAL: Cek Hard Constraint terhadap reservasi yang sedang di-approve
                    // Penalty tinggi tapi tidak terlalu ekstrem
                    if ($geneA['day'] == $dayOfWeek && $geneA['room_id'] == $reservation->room_id) {
                        $gStart = strtotime($geneA['start_time']);
                        $gEnd = strtotime($geneA['end_time']);
                        if ($gStart < $end && $gEnd > $start) {
                            $penalty += 300; // Penalty tinggi tapi masih bisa di-evolve
                            $criticalPenalty += 300; // Track konflik kritis
                        }
                    }

                    // Cek konflik antar jadwal akademik
                    for ($j = $i + 1; $j < $totalGenes; $j++) {
                        $geneB = $chromosome[$j];

                        if ($geneA['day'] !== $geneB['day']) {
                            continue;
                        }

                        $startA = strtotime($geneA['start_time']);
                        $endA = strtotime($geneA['end_time']);
                        $startB = strtotime($geneB['start_time']);
                        $endB = strtotime($geneB['end_time']);

                        if ($startA < $endB && $endA > $startB) {
                            // HARD CONSTRAINT: Ruangan sama bentrok
                            if ($geneA['room_id'] === $geneB['room_id']) {
                                $penalty += 100;
                            }
                            // HARD CONSTRAINT: Dosen sama bentrok
                            if ($geneA['lecturer_id'] === $geneB['lecturer_id']) {
                                $penalty += 100;
                            }
                        }
                    }
                }

                $fitness = 1 / (1 + $penalty);
                $fitnessScores[] = $fitness;

                // Track solusi terbaik secara keseluruhan
                if ($fitness > $bestOverallScore) {
                    $bestOverallScore = $fitness;
                    $bestOverallChromosome = $chromosome;
                }

                // Early termination: Solusi sempurna ditemukan (tidak ada konflik dengan reservasi)
                if ($criticalPenalty === 0 && $penalty <= 5) {
                    // Terima solusi dengan soft constraint minor (jam siang)
                    $this->saveBestSchedule($chromosome);

                    return true;
                }
            }

            $bestScore = max($fitnessScores);

            // Early termination: Solusi sempurna ditemukan
            if ($bestScore === 1.0) {
                $bestIndex = array_search($bestScore, $fitnessScores);
                $this->saveBestSchedule($population[$bestIndex]);

                return true;
            }

            // 5. Evolusi generasi berikutnya
            $newPopulation = [];
            while (count($newPopulation) < $this->populationSize) {
                $parent1 = $this->selection($population, $fitnessScores);
                $parent2 = $this->selection($population, $fitnessScores);

                // Crossover parsial: hanya pada gen yang dipindah
                $offspring = $parent1;
                foreach ($offspring as &$gene) {
                    if (in_array($gene['course_id'], $conflictCourseIds) && rand(0, 1)) {
                        foreach ($parent2 as $p2Gene) {
                            if ($p2Gene['course_id'] === $gene['course_id']) {
                                $gene = $p2Gene;
                                break;
                            }
                        }
                    }
                }

                // Mutasi parsial: hanya gen yang bentrok dengan rate adaptif
                foreach ($offspring as &$gene) {
                    if (in_array($gene['course_id'], $conflictCourseIds)) {
                        // Rate mutasi adaptif: lebih tinggi di awal, menurun seiring generasi
                        $adaptiveMutationRate = $this->mutationRate * (1.5 + (1 - $generation / $this->maxGenerations));

                        if (rand(0, 100) / 100 < $adaptiveMutationRate) {
                            $gene['room_id'] = $roomIds[array_rand($roomIds)];
                            $gene['day'] = $days[array_rand($days)];

                            $course = $this->courses[$gene['course_id']];
                            $newStartString = $this->allowedStartTimes[array_rand($this->allowedStartTimes)];

                            $startTime = Carbon::createFromFormat('H:i', $newStartString);
                            $endTime = (clone $startTime)->addMinutes($course->sks * $this->minutesPerSks);

                            $gene['start_time'] = $startTime->format('H:i:s');
                            $gene['end_time'] = $endTime->format('H:i:s');
                        }
                    }
                }

                $newPopulation[] = $offspring;
            }
            $population = $newPopulation;
        }

        // Fallback: Evaluasi solusi terbaik yang ditemukan
        // Cek apakah solusi terbaik tidak memiliki konflik KRITIS dengan reservasi target
        $criticalConflict = false;
        foreach ($bestOverallChromosome as $gene) {
            if ($gene['day'] == $dayOfWeek && $gene['room_id'] == $reservation->room_id) {
                $gStart = strtotime($gene['start_time']);
                $gEnd = strtotime($gene['end_time']);
                if ($gStart < $end && $gEnd > $start) {
                    $criticalConflict = true;
                    break;
                }
            }
        }

        // Jika tidak ada konflik kritis dan tidak ada pelanggaran hard constraint lainnya, terima solusi terbaik
        if (! $criticalConflict && $bestOverallChromosome !== null && $bestOverallScore > 0.0099) {
            $this->saveBestSchedule($bestOverallChromosome);

            return true;
        }

        // Gagal menemukan solusi yang layak
        return false;
    }

    /**
     * Check if a specific slot is available (No conflict with Academic Schedules or Approved Reservations).
     */
    private function isSlotAvailable(int $roomId, int $day, string $start, string $end, int $excludeScheduleId, Reservation $currentReservation): bool
    {
        // Check Academic Schedule conflict (Freeze others)
        $academicConflict = AcademicSchedule::where('id', '!=', $excludeScheduleId)
            ->where('room_id', $roomId)
            ->where('day', $day)
            ->where(function ($query) use ($start, $end) {
                $query->where('start_time', '<', $end)
                      ->where('end_time', '>', $start);
            })
            ->exists();

        if ($academicConflict) return false;

        // Check against the current reservation (if it's the same day/room)
        $currentResDay = Carbon::parse($currentReservation->date)->dayOfWeekIso;
        if ($roomId === $currentReservation->room_id && $day === $currentResDay) {
            if ($start < $currentReservation->end_time && $end > $currentReservation->start_time) {
                return false;
            }
        }

        // Check against other approved reservations
        $reservationConflict = Reservation::where('status', 'approved')
            ->where('room_id', $roomId)
            ->whereRaw("strftime('%w', date) = ?", [($day % 7)])
            ->where(function ($query) use ($start, $end) {
                $query->where('start_time', '<', $end)
                      ->where('end_time', '>', $start);
            })
            ->exists();

        if ($reservationConflict) return false;

        return true;
    }
}
