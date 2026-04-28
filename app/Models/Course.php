<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Course extends Model
{
    protected $fillable = [
        'name',
        'code',
        'sks',
        'semester',
        'expected_students',
    ];

    /**
     * Get the academic schedules for the course.
     */
    public function academicSchedules(): HasMany
    {
        return $this->hasMany(AcademicSchedule::class);
    }
}
