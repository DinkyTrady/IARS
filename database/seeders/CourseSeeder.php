<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\Lecturer;
use Illuminate\Database\Seeder;

class CourseSeeder extends Seeder
{
    public function run(): void
    {
        // Ambil dosen yang sudah di-seed
        $lecturers = Lecturer::all();

        $courses = [
            ['name' => 'Pemrograman Web Lanjut', 'code' => 'IF201', 'sks' => 3, 'semester' => 3, 'expected_students' => 35],
            ['name' => 'Kecerdasan Buatan', 'code' => 'IF301', 'sks' => 3, 'semester' => 5, 'expected_students' => 45],
            ['name' => 'Basis Data', 'code' => 'IF102', 'sks' => 4, 'semester' => 2, 'expected_students' => 40],
            ['name' => 'Jaringan Komputer', 'code' => 'IF205', 'sks' => 3, 'semester' => 4, 'expected_students' => 30],
            ['name' => 'Matematika Diskrit', 'code' => 'IF101', 'sks' => 2, 'semester' => 1, 'expected_students' => 50],
            ['name' => 'Struktur Data & Algoritma', 'code' => 'IF103', 'sks' => 3, 'semester' => 2, 'expected_students' => 40],
            ['name' => 'Sistem Operasi', 'code' => 'IF202', 'sks' => 3, 'semester' => 3, 'expected_students' => 35],
        ];

        foreach ($courses as $index => $course) {
            Course::create([
                ...$course,
                'lecturer_id' => $lecturers->isNotEmpty()
                    ? $lecturers[$index % $lecturers->count()]->id
                    : null,
            ]);
        }
    }
}
