<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Lecturer extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'nidn',
        'email',
        'phone',
    ];

    /**
     * Get the academic schedules for the lecturer.
     */
    public function academicSchedules(): HasMany
    {
        return $this->hasMany(AcademicSchedule::class);
    }

    /**
     * Get courses assigned to this lecturer.
     */
    public function courses(): HasMany
    {
        return $this->hasMany(Course::class);
    }
}
