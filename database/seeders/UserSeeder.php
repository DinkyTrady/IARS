<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'name' => 'Administrator',
            'email' => 'admin@kampus.ac.id',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        User::create([
            'name' => 'Budi Mahasiswa',
            'email' => 'budi@kampus.ac.id',
            'password' => Hash::make('password'),
            'role' => 'user',
        ]);
        
        User::create([
            'name' => 'Siti Dosen',
            'email' => 'siti@kampus.ac.id',
            'password' => Hash::make('password'),
            'role' => 'user',
        ]);
    }
}
