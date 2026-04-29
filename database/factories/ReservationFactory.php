<?php

namespace Database\Factories;

use App\Models\Reservation;
use App\Models\Room;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Reservation>
 */
class ReservationFactory extends Factory
{
    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'room_id' => Room::factory(),
            'activity_name' => $this->faker->words(3, true),
            'description' => $this->faker->sentence(),
            'date' => $this->faker->dateTimeBetween('now', '+1 month')->format('Y-m-d'),
            'start_time' => '09:00:00',
            'end_time' => '11:00:00',
            'status' => 'pending',
            'note' => null,
        ];
    }

    public function approved(): static
    {
        return $this->state(['status' => 'approved']);
    }

    public function rejected(): static
    {
        return $this->state(['status' => 'rejected']);
    }

    /** Reservasi yang sudah diberi catatan bentrok oleh sistem. */
    public function withConflictNote(): static
    {
        return $this->state([
            'note' => 'Peringatan Sistem: Ruangan bentrok dengan jadwal perkuliahan.',
        ]);
    }
}
