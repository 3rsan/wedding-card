<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Memory extends Model
{
    use HasFactory;

    protected $fillable = ['wedding_id', 'first_name', 'last_name', 'message', 'media_type', 'media_path', 'is_approved'];

    protected $casts = [
        'is_approved' => 'boolean',
    ];

    public function wedding(): BelongsTo
    {
        return $this->belongsTo(Wedding::class);
    }
}
