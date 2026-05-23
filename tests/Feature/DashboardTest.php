<?php

use App\Models\Room;
use App\Models\User;

test('guests are redirected to the login page', function () {
    $response = $this->get(route('dashboard'));
    $response->assertRedirect(route('login'));
});

test('authenticated users can visit the dashboard', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->get(route('dashboard'));
    $response->assertOk();
});

test('room list renders successfully on dashboard', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    Room::create([
        'name' => 'Ruang Teori 101',
        'code' => 'RT-101',
        'building' => 'Gedung A',
        'floor' => 1,
        'capacity' => 40,
        'facilities' => ['AC', 'Projector'],
        'status' => 'available',
    ]);

    $response = $this->get(route('dashboard'));
    $response->assertSee('Ruang Teori 101');
});
