<?php

use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');
    Route::view('reservations', 'reservations')->name('reservations.index');
    Route::view('reservations/create', 'create-reservation-page')->name('reservations.create');
    
    // Admin Routes
    Route::middleware(['can:admin'])->group(function () {
        Route::view('admin/reservations', 'admin.reservations')->name('admin.reservations');
        Route::view('admin/rooms', 'admin.rooms-page')->name('admin.rooms');
        Route::view('admin/courses', 'admin.courses-page')->name('admin.courses');
        Route::view('admin/lecturers', 'admin.lecturers-page')->name('admin.lecturers');
        Route::view('admin/schedules', 'admin.generate-schedule-page')->name('admin.schedules');
    });
});

require __DIR__.'/settings.php';
