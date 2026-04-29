<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\AcademicSchedule;
use Illuminate\Support\Carbon;

$start = Carbon::parse('09:00');
$end = Carbon::parse('11:00');
$room_id = 1; // Assuming room 1
$dayOfWeek = 2; // Assuming Tuesday

$conflictSchedule = AcademicSchedule::where('room_id', $room_id)
    //->where('day', $dayOfWeek)
    ->where(function ($query) use ($start, $end) {
        $query->whereTime('start_time', '<', $end->format('H:i:s'))
              ->whereTime('end_time', '>', $start->format('H:i:s'));
    })
    ->get();

echo "Conflict count: " . $conflictSchedule->count() . "\n";
foreach ($conflictSchedule as $c) {
    echo "Found conflict: Room {$c->room_id}, Day {$c->day}, {$c->start_time} - {$c->end_time}\n";
}
