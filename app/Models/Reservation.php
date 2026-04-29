<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Reservation extends Model
{
    use HasFactory;

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

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'date' => 'date',
        ];
    }

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

    /**
     * Scope untuk reservasi yang masih aktif (belum rejected/canceled).
     */
    public function scopeActive($query): Builder
    {
        return $query->whereNotIn('status', ['rejected', 'canceled']);
    }
}
