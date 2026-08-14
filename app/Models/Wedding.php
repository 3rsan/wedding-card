<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Wedding extends Model
{
    use HasFactory;

    protected $appends = ['cover_image_url'];

    protected $fillable = [
        'slug', 'groom_name', 'bride_name', 'wedding_date', 'theme',
        'theme_colors', 'venues', 'cover_image', 'hero_video',
        'is_published', 'owner_user_id', 'default_theme_colors',
    ];

    protected $casts = [
        'wedding_date' => 'date',
        'theme_colors' => 'array',
        'default_theme_colors' => 'array',
        'venues' => 'array',
        'is_published' => 'boolean',
    ];

    public function guests(): HasMany
    {
        return $this->hasMany(Guest::class);
    }

    public function memories(): HasMany
    {
        return $this->hasMany(Memory::class);
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    protected function coverImageUrl(): Attribute
    {
        return Attribute::get(fn () => $this->cover_image
            ? url('/api/media/' . $this->cover_image)
            : null);
    }
}
