<?php

namespace Database\Factories;

use App\Models\Course;
use App\Models\Lecturer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Course>
 */
class CourseFactory extends Factory
{
    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'name' => $this->faker->words(3, true),
            'code' => strtoupper($this->faker->unique()->bothify('MK###')),
            'sks' => $this->faker->randomElement([2, 3, 4]),
            'semester' => $this->faker->numberBetween(1, 8),
            'expected_students' => $this->faker->numberBetween(20, 40),
            'lecturer_id' => Lecturer::factory(),
        ];
    }
}
