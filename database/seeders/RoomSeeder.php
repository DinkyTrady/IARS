<?php

namespace Database\Seeders;

use App\Models\Room;
use Illuminate\Database\Seeder;

class RoomSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $rooms = [
            [
                'name' => 'Lab Komputer 1',
                'code' => 'LAB-01',
                'building' => 'Gedung A',
                'floor' => 2,
                'capacity' => 30,
                'facilities' => json_encode(['AC', 'Projector', 'PCs', 'LAN']),
                'status' => 'available',
            ],
            [
                'name' => 'Ruang Teori 101',
                'code' => 'RT-101',
                'building' => 'Gedung B',
                'floor' => 1,
                'capacity' => 40,
                'facilities' => json_encode(['AC', 'Whiteboard', 'Projector']),
                'status' => 'available',
            ],
            [
                'name' => 'Aula Utama',
                'code' => 'AULA-01',
                'building' => 'Gedung C',
                'floor' => 1,
                'capacity' => 200,
                'facilities' => json_encode(['Sound System', 'AC', 'Stage', 'Projector']),
                'status' => 'available',
            ],
        ];

        foreach ($rooms as $room) {
            Room::create($room);
        }
    }
}
