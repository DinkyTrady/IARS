<?php

namespace Database\Seeders;

use App\Models\Course;
use Illuminate\Database\Seeder;

class CourseSeeder extends Seeder
{
    public function run(): void
    {
        Course::create([
            'name' => 'Pemrograman Web Lanjut',
            'code' => 'IF201',
            'sks' => 3,
            'semester' => 3,
            'expected_students' => 35,
        ]);

        Course::create([
            'name' => 'Kecerdasan Buatan',
            'code' => 'IF301',
            'sks' => 3,
            'semester' => 5,
            'expected_students' => 45,
        ]);

        Course::create([
            'name' => 'Basis Data',
            'code' => 'IF102',
            'sks' => 4,
            'semester' => 2,
            'expected_students' => 40,
        ]);

        Course::create([
            'name' => 'Jaringan Komputer',
            'code' => 'IF205',
            'sks' => 3,
            'semester' => 4,
            'expected_students' => 30,
        ]);
        
        Course::create([
            'name' => 'Matematika Diskrit',
            'code' => 'IF101',
            'sks' => 2,
            'semester' => 1,
            'expected_students' => 50,
        ]);
    }
}
