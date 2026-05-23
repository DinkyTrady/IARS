<?php

namespace Database\Factories;

use App\Models\Room;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Room>
 */
class RoomFactory extends Factory
{
    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'name' => 'Ruang '.$this->faker->bothify('##?'),
            'code' => strtoupper($this->faker->bothify('R###')),
            'building' => $this->faker->randomElement(['Gedung A', 'Gedung B', 'Gedung C']),
            'floor' => $this->faker->numberBetween(1, 4),
            'capacity' => $this->faker->randomElement([30, 40, 50, 60]),
            'facilities' => ['Proyektor', 'AC'],
            'status' => 'available',
        ];
    }

    public function unavailable(): static
    {
        return $this->state(['status' => 'unavailable']);
    }
}
