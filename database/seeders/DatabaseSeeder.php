<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Admin User
        User::factory()->create([
            'name' => 'Admin Sistem',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        // Regular User
        User::factory()->create([
            'name' => 'Mahasiswa Contoh',
            'email' => 'mahasiswa@example.com',
            'password' => Hash::make('password'),
            'role' => 'user',
        ]);

        $this->call([
            RoomSeeder::class,
            LecturerSeeder::class,
            CourseSeeder::class,
        ]);
    }
}
