<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Reservation extends Model
{
    protected $fillable = [
        'user_id',
        'room_id',
        'activity_name',
        'description',
        'date',
        'start_time',
        'end_time',
        'status',
        'note',
    ];

    /**
     * Get the user that owns the reservation.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the room that is reserved.
     */
    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }
}
