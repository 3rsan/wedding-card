<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
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

    protected $appends = ['media_url'];

    public function wedding(): BelongsTo
    {
        return $this->belongsTo(Wedding::class);
    }

    protected function mediaUrl(): Attribute
    {
        // R2'nin public URL'i yerine kendi backend'imiz üzerinden proxy'liyoruz
        // (bkz: .r2.dev domain'inin bazı ISP'lerde TLS seviyesinde engellenmesi)
        return Attribute::get(fn () => $this->media_path
            ? url('/api/media/' . $this->media_path)
            : null);
    }
}
