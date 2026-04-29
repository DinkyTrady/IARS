<?php

use App\Models\AcademicSchedule;
use App\Models\Course;
use App\Models\Lecturer;
use App\Models\Reservation;
use App\Models\Room;
use App\Models\User;
use App\Services\GeneticAlgorithmService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;

uses(RefreshDatabase::class);

// ──────────────────────────────────────────────────────────────
// Helper: buat user admin
// ──────────────────────────────────────────────────────────────
function makeAdmin(): User
{
    return User::factory()->admin()->create();
}

function makeUser(): User
{
    return User::factory()->asUser()->create();
}

// ──────────────────────────────────────────────────────────────
// GRUP 1: Pengajuan Reservasi (User)
// ──────────────────────────────────────────────────────────────

describe('User mengajukan reservasi', function () {

    it('berhasil submit dan tersimpan sebagai pending tanpa bentrok', function () {
        $user = makeUser();
        $room = Room::factory()->create(['status' => 'available']);

        actingAs($user);

        Livewire::test('create-reservation')
            ->set('room_id', $room->id)
            ->set('activity_name', 'Rapat BEM')
            ->set('date', now()->addDay()->format('Y-m-d'))
            ->set('start_time', '09:00')
            ->set('end_time', '11:00')
            ->call('save');

        $this->assertDatabaseHas('reservations', [
            'activity_name' => 'Rapat BEM',
            'status' => 'pending',
            'note' => null,
        ]);
    });

    it('masih bisa submit meski bentrok dengan jadwal kuliah, dengan catatan sistem di kolom note', function () {
        $user = makeUser();
        $room = Room::factory()->create(['status' => 'available']);
        $lecturer = Lecturer::factory()->create();
        $course = Course::factory()->create(['lecturer_id' => $lecturer->id, 'expected_students' => 20]);

        // Jadwal kuliah: Senin (1) jam 09:00 – 11:00 di ruang yang sama
        $targetDate = now()->next('Monday'); // ambil hari Senin berikutnya
        AcademicSchedule::factory()->create([
            'room_id' => $room->id,
            'course_id' => $course->id,
            'lecturer_id' => $lecturer->id,
            'day' => 1, // Senin
            'start_time' => '09:00:00',
            'end_time' => '11:00:00',
        ]);

        actingAs($user);

        Livewire::test('create-reservation')
            ->set('room_id', $room->id)
            ->set('activity_name', 'Seminar Prodi')
            ->set('date', $targetDate->format('Y-m-d'))
            ->set('start_time', '09:00')
            ->set('end_time', '11:00')
            ->call('save');

        // Reservasi tetap tersimpan (tidak diblokir)
        $reservation = Reservation::where('activity_name', 'Seminar Prodi')->first();
        expect($reservation)->not->toBeNull();
        expect($reservation->status)->toBe('pending');
        // Kolom note harus berisi peringatan bentrok
        expect($reservation->note)->toContain('Peringatan Sistem');
    });

    it('masih bisa submit meski bentrok dengan reservasi lain yang sudah approved, dengan catatan sistem', function () {
        $user = makeUser();
        $otherUser = makeUser();
        $room = Room::factory()->create(['status' => 'available']);

        // Reservasi yang sudah approved di slot yang sama
        Reservation::factory()->approved()->create([
            'room_id' => $room->id,
            'date' => now()->addDays(2)->format('Y-m-d'),
            'start_time' => '10:00:00',
            'end_time' => '12:00:00',
            'user_id' => $otherUser->id,
        ]);

        actingAs($user);

        Livewire::test('create-reservation')
            ->set('room_id', $room->id)
            ->set('activity_name', 'Rapat UKM')
            ->set('date', now()->addDays(2)->format('Y-m-d'))
            ->set('start_time', '10:00')
            ->set('end_time', '12:00')
            ->call('save');

        $reservation = Reservation::where('activity_name', 'Rapat UKM')->first();
        expect($reservation)->not->toBeNull();
        expect($reservation->status)->toBe('pending');
        expect($reservation->note)->not->toBeNull()
            ->and($reservation->note)->toContain('Peringatan Sistem');
    });

});

// ──────────────────────────────────────────────────────────────
// GRUP 2: Partial GA – resolveConflictForReservation()
// ──────────────────────────────────────────────────────────────

describe('Partial GA resolveConflictForReservation', function () {

    it('mengembalikan true jika tidak ada jadwal yang bentrok', function () {
        $room = Room::factory()->create(['status' => 'available']);
        $user = makeUser();

        // Reservasi di hari Sabtu (6) – tidak ada jadwal kuliah di sana
        $reservation = Reservation::factory()->create([
            'room_id' => $room->id,
            'date' => now()->next('Saturday')->format('Y-m-d'),
            'start_time' => '09:00:00',
            'end_time' => '11:00:00',
            'user_id' => $user->id,
        ]);

        $service = new GeneticAlgorithmService;
        $result = $service->resolveConflictForReservation($reservation);

        expect($result)->toBeTrue();
    });

    it('mengembalikan true dan memindahkan jadwal kuliah yang bentrok jika ada ruangan lain tersedia', function () {
        // Buat 2 ruangan agar GA punya ruang untuk geser
        $roomA = Room::factory()->create(['status' => 'available', 'capacity' => 50]);
        $roomB = Room::factory()->create(['status' => 'available', 'capacity' => 50]);

        $lecturer = Lecturer::factory()->create();
        $course = Course::factory()->create([
            'lecturer_id' => $lecturer->id,
            'sks' => 2,
            'expected_students' => 30,
        ]);

        $targetDate = now()->next('Monday');
        $dayOfWeek = 1; // Senin

        // Jadwal kuliah di Room A jam 09:00 hari Senin
        AcademicSchedule::create([
            'course_id' => $course->id,
            'lecturer_id' => $lecturer->id,
            'room_id' => $roomA->id,
            'day' => $dayOfWeek,
            'start_time' => '09:00:00',
            'end_time' => '10:40:00',
        ]);

        $user = makeUser();
        $reservation = Reservation::factory()->create([
            'room_id' => $roomA->id,
            'date' => $targetDate->format('Y-m-d'),
            'start_time' => '09:00:00',
            'end_time' => '11:00:00',
            'user_id' => $user->id,
        ]);

        $service = new GeneticAlgorithmService;
        $result = $service->resolveConflictForReservation($reservation);

        // Partial GA harus berhasil menemukan solusi
        expect($result)->toBeTrue();

        // Jadwal yang tergusur harus TIDAK lagi di Room A jam 09:00 hari Senin
        $schedule = AcademicSchedule::where('course_id', $course->id)->first();
        $stillConflicts = ($schedule->room_id == $roomA->id)
            && ($schedule->day == $dayOfWeek)
            && (strtotime($schedule->start_time) < strtotime('11:00:00'))
            && (strtotime($schedule->end_time) > strtotime('09:00:00'));

        expect($stillConflicts)->toBeFalse();
    });

});

// ──────────────────────────────────────────────────────────────
// GRUP 3: Admin Approval dengan integrasi Partial GA
// ──────────────────────────────────────────────────────────────

describe('Admin approve reservasi dengan Partial GA', function () {

    it('berhasil approve reservasi tanpa bentrok, status menjadi approved', function () {
        $admin = makeAdmin();
        $room = Room::factory()->create(['status' => 'available']);
        $user = makeUser();

        $reservation = Reservation::factory()->create([
            'room_id' => $room->id,
            'date' => now()->next('Saturday')->format('Y-m-d'), // Sabtu = tidak ada kuliah
            'start_time' => '09:00:00',
            'end_time' => '11:00:00',
            'user_id' => $user->id,
            'status' => 'pending',
        ]);

        actingAs($admin);

        Livewire::test('admin-reservations')
            ->call('approve', $reservation->id);

        expect($reservation->fresh()->status)->toBe('approved');
    });

    it('berhasil approve reservasi yang bentrok dan GA memindahkan jadwal kuliah', function () {
        $admin = makeAdmin();
        $roomA = Room::factory()->create(['status' => 'available', 'capacity' => 50]);
        $roomB = Room::factory()->create(['status' => 'available', 'capacity' => 50]);

        $lecturer = Lecturer::factory()->create();
        $course = Course::factory()->create([
            'lecturer_id' => $lecturer->id,
            'sks' => 2,
            'expected_students' => 30,
        ]);

        $targetDate = now()->next('Monday');

        AcademicSchedule::create([
            'course_id' => $course->id,
            'lecturer_id' => $lecturer->id,
            'room_id' => $roomA->id,
            'day' => 1,
            'start_time' => '09:00:00',
            'end_time' => '10:40:00',
        ]);

        $user = makeUser();
        $reservation = Reservation::factory()->create([
            'room_id' => $roomA->id,
            'date' => $targetDate->format('Y-m-d'),
            'start_time' => '09:00:00',
            'end_time' => '11:00:00',
            'user_id' => $user->id,
            'status' => 'pending',
            'note' => 'Peringatan Sistem: Bentrok dengan jadwal kuliah.',
        ]);

        actingAs($admin);

        Livewire::test('admin-reservations')
            ->call('approve', $reservation->id);

        expect($reservation->fresh()->status)->toBe('approved');
    });

    it('gagal approve dan tidak merubah status jika GA tidak bisa temukan slot kosong', function () {
        $admin = makeAdmin();

        // Hanya ada 1 ruangan dengan 1 hari (Senin) dan sudah padat semua slot
        $room = Room::factory()->create(['status' => 'available', 'capacity' => 50]);
        $lecturer = Lecturer::factory()->create();

        // Isi semua slot waktu yang diizinkan dengan jadwal kuliah di ruangan yang sama
        $allowedStartTimes = ['08:00', '09:00', '10:00', '11:00', '13:00', '14:00', '15:00'];
        foreach ($allowedStartTimes as $startTime) {
            $course = Course::factory()->create([
                'lecturer_id' => Lecturer::factory()->create()->id,
                'sks' => 2,
                'expected_students' => 30,
            ]);
            foreach (range(1, 5) as $day) {
                AcademicSchedule::create([
                    'course_id' => $course->id,
                    'lecturer_id' => $lecturer->id,
                    'room_id' => $room->id,
                    'day' => $day,
                    'start_time' => $startTime.':00',
                    'end_time' => date('H:i:s', strtotime($startTime.':00') + 100 * 60),
                ]);
            }
        }

        $user = makeUser();
        $reservation = Reservation::factory()->create([
            'room_id' => $room->id,
            'date' => now()->next('Monday')->format('Y-m-d'),
            'start_time' => '09:00:00',
            'end_time' => '11:00:00',
            'user_id' => $user->id,
            'status' => 'pending',
        ]);

        actingAs($admin);

        Livewire::test('admin-reservations')
            ->call('approve', $reservation->id);

        // Status TIDAK berubah karena GA gagal
        expect($reservation->fresh()->status)->toBe('pending');
    });

});
