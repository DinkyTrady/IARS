<?php

use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');
    Route::view('reservations', 'reservations')->name('reservations.index');
    
    // Admin Routes
    Route::middleware(['can:admin'])->group(function () {
        Route::view('admin/reservations', 'admin.reservations')->name('admin.reservations');
    });
});

require __DIR__.'/settings.php';
