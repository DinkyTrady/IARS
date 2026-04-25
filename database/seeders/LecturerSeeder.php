<?php

namespace Database\Seeders;

use App\Models\Lecturer;
use Illuminate\Database\Seeder;

class LecturerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $lecturers = [
            [
                'name' => 'Dosen Pengampu A',
                'nidn' => '1111111111',
                'email' => 'dosen.a@example.com',
            ],
            [
                'name' => 'Dosen Pengampu B',
                'nidn' => '2222222222',
                'email' => 'dosen.b@example.com',
            ],
            [
                'name' => 'Dosen Pengampu C',
                'nidn' => '3333333333',
                'email' => 'dosen.c@example.com',
            ],
        ];

        foreach ($lecturers as $lecturer) {
            Lecturer::create($lecturer);
        }
    }
}
