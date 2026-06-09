<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Shop extends Model
{
    protected $fillable = [
        'name',
        'secondary_name',
        'slug',
        'confirmed',
        'show_under_product',
        'description',
        'person_responsible_name',
        'person_responsible_email',
        'website_show',
        'order',
        'latitude',
        'longitude',
        'state_id',
        'address',
        'open_time',
        'close_time',
        'open_time_friday',
        'close_time_friday',
        'open_time_thursday',
        'close_time_thursday',
        'off',
    ];

    protected function casts(): array
    {
        return [
            'confirmed' => 'boolean',
            'show_under_product' => 'boolean',
            'latitude' => 'decimal:8',
            'longitude' => 'decimal:8',
            'order' => 'integer',
            'off' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Shop $shop): void {
            if ($shop->order === null) {
                $shop->order = (static::max('order') ?? 0) + 1;
            }
        });
    }

    public function state(): BelongsTo
    {
        return $this->belongsTo(State::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(Image::class);
    }

    public function phones(): HasMany
    {
        return $this->hasMany(Phone::class);
    }

    public function links(): HasMany
    {
        return $this->hasMany(Link::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    public function partsCategories(): BelongsToMany
    {
        return $this->belongsToMany(PartsCategory::class, 'parts_category_shop');
    }

    public function parts(): BelongsToMany
    {
        return $this->belongsToMany(Part::class, 'part_shop');
    }

    public function companies(): BelongsToMany
    {
        return $this->belongsToMany(Company::class, 'company_shops');
    }

    public function scopeVisibleUnderProduct(Builder $query): void
    {
        $query->where('show_under_product', true);
    }

    public function scopeOrdered(Builder $query): void
    {
        $query->orderByDesc('order')->orderBy('id');
    }

    public function scopeConfirmed(Builder $query): void
    {
        $query->where('confirmed', true);
    }
}
