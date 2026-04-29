<?php

namespace Database\Factories;

use App\Models\AcademicSchedule;
use App\Models\Course;
use App\Models\Lecturer;
use App\Models\Room;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AcademicSchedule>
 */
class AcademicScheduleFactory extends Factory
{
    /** @return array<string, mixed> */
    public function definition(): array
    {
        $startHour = $this->faker->randomElement([8, 9, 10, 11, 13, 14, 15]);
        $sks = 2;

        return [
            'course_id' => Course::factory(),
            'lecturer_id' => Lecturer::factory(),
            'room_id' => Room::factory(),
            'day' => $this->faker->numberBetween(1, 5),
            'start_time' => sprintf('%02d:00:00', $startHour),
            'end_time' => sprintf('%02d:40:00', $startHour + $sks - 1),
        ];
    }
}
