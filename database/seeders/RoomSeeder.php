<?php

namespace Database\Seeders;

use App\Models\Room;
use Illuminate\Database\Seeder;

class RoomSeeder extends Seeder
{
    public function run(): void
    {
        Room::create([
            'name' => 'Ruang Teori 101',
            'code' => 'RT-101',
            'building' => 'Gedung A',
            'floor' => 1,
            'capacity' => 40,
            'facilities' => ['AC', 'Projector', 'Whiteboard'],
            'status' => 'available',
        ]);

        Room::create([
            'name' => 'Ruang Teori 102',
            'code' => 'RT-102',
            'building' => 'Gedung A',
            'floor' => 1,
            'capacity' => 30,
            'facilities' => ['AC', 'Projector'],
            'status' => 'available',
        ]);

        Room::create([
            'name' => 'Laboratorium Komputer 1',
            'code' => 'LAB-01',
            'building' => 'Gedung B',
            'floor' => 2,
            'capacity' => 25,
            'facilities' => ['AC', 'PC', 'Projector'],
            'status' => 'available',
        ]);
        
        Room::create([
            'name' => 'Aula Besar',
            'code' => 'AULA',
            'building' => 'Gedung C',
            'floor' => 1,
            'capacity' => 100,
            'facilities' => ['AC', 'Projector', 'Sound System'],
            'status' => 'available',
        ]);
    }
}
