<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Course extends Model
{
    protected $fillable = [
        'name',
        'code',
        'sks',
        'semester',
        'expected_students',
        'lecturer_id',
    ];

    /**
     * Get the lecturer assigned to this course.
     */
    public function lecturer(): BelongsTo
    {
        return $this->belongsTo(Lecturer::class);
    }

    /**
     * Get the academic schedules for the course.
     */
    public function academicSchedules(): HasMany
    {
        return $this->hasMany(AcademicSchedule::class);
    }
}
