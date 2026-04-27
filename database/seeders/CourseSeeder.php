<?php

namespace Database\Seeders;

use App\Models\Course;
use Illuminate\Database\Seeder;

class CourseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $courses = [
            [
                'name' => 'Pemrograman Web',
                'code' => 'IF-101',
                'sks' => 3,
                'semester' => 4,
            ],
            [
                'name' => 'Basis Data Lanjut',
                'code' => 'IF-201',
                'sks' => 3,
                'semester' => 4,
            ],
            [
                'name' => 'Kecerdasan Buatan',
                'code' => 'IF-301',
                'sks' => 4,
                'semester' => 6,
            ],
            [
                'name' => 'Metodologi Penelitian',
                'code' => 'IF-401',
                'sks' => 2,
                'semester' => 4,
            ],
        ];

        foreach ($courses as $course) {
            Course::create($course);
        }
    }
}
