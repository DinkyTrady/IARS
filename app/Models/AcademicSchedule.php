<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AcademicSchedule extends Model
{
    protected $fillable = [
        'course_id',
        'lecturer_id',
        'room_id',
        'day',
        'start_time',
        'end_time',
    ];

    /**
     * Get the course that belongs to the schedule.
     */
    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    /**
     * Get the lecturer that belongs to the schedule.
     */
    public function lecturer(): BelongsTo
    {
        return $this->belongsTo(Lecturer::class);
    }

    /**
     * Get the room that belongs to the schedule.
     */
    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }
}
