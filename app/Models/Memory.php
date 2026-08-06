<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

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
        return Attribute::get(function () {
            if (! $this->media_path) {
                return null;
            }

            /** @var \Illuminate\Filesystem\FilesystemAdapter $disk */
            $disk = Storage::disk('s3');

            return $disk->url($this->media_path);
        });
    }
}