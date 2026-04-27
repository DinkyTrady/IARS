<?php

namespace Database\Seeders;

use App\Models\AcademicSchedule;
use App\Models\Course;
use App\Models\Lecturer;
use App\Models\Room;
use Illuminate\Database\Seeder;

class AcademicScheduleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $course = Course::where('code', 'IF-101')->first();
        $lecturer = Lecturer::first();
        $room = Room::where('code', 'LAB-01')->first();

        AcademicSchedule::create([
            'course_id' => $course->id,
            'lecturer_id' => $lecturer->id,
            'room_id' => $room->id,
            'day' => 1, // Monday
            'start_time' => '08:00:00',
            'end_time' => '10:30:00',
        ]);

        // Add another one in different room
        $room2 = Room::where('code', 'RT-101')->first();
        AcademicSchedule::create([
            'course_id' => Course::where('code', 'IF-201')->first()->id,
            'lecturer_id' => Lecturer::find(2)->id,
            'room_id' => $room2->id,
            'day' => 2, // Tuesday
            'start_time' => '13:00:00',
            'end_time' => '15:30:00',
        ]);
    }
}
