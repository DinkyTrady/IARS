<?php

namespace Database\Seeders;

use App\Models\Lecturer;
use Illuminate\Database\Seeder;

class LecturerSeeder extends Seeder
{
    public function run(): void
    {
        Lecturer::create([
            'name' => 'Dr. Andi Saputra, M.Kom',
            'nidn' => '1234567890',
            'email' => 'andi@kampus.ac.id',
            'phone' => '081234567890',
        ]);

        Lecturer::create([
            'name' => 'Prof. Budi Santoso, Ph.D',
            'nidn' => '0987654321',
            'email' => 'budi.s@kampus.ac.id',
            'phone' => '089876543210',
        ]);

        Lecturer::create([
            'name' => 'Siti Aminah, M.T',
            'nidn' => '1122334455',
            'email' => 'siti.a@kampus.ac.id',
            'phone' => '085678901234',
        ]);
    }
}
