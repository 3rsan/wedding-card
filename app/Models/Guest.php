<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Guest extends Model
{
    use HasFactory;

    protected $fillable = ['wedding_id', 'display_name', 'invite_token', 'max_guests', 'phone'];

    protected static function booted(): void
    {
        static::creating(function (Guest $guest) {
            if (empty($guest->invite_token)) {
                $guest->invite_token = Str::random(16);
            }
        });
    }

    public function wedding(): BelongsTo
    {
        return $this->belongsTo(Wedding::class);
    }

    public function rsvps(): HasMany
    {
        return $this->hasMany(Rsvp::class);
    }

    public function latestRsvp()
    {
        return $this->rsvps()->latest()->first();
    }
}
