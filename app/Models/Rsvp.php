<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Rsvp extends Model
{
    use HasFactory;

    protected $fillable = ['guest_id', 'attending', 'guest_count', 'attendee_names', 'note'];

    protected $casts = [
        'attending' => 'boolean',
        'attendee_names' => 'array',
    ];

    public function guest(): BelongsTo
    {
        return $this->belongsTo(Guest::class);
    }
}
