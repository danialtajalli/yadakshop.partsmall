<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Laravel\Scout\Searchable;

class Company extends Model
{
    use Searchable;

    protected $fillable = [
        'name',
        'description',
        'slug',
        'country',
        'wage_strike',
    ];

    protected function casts(): array
    {
        return [
            'wage_strike' => 'float',
        ];
    }

    public function toSearchableArray(): array
    {
        $this->loadMissing('cars');

        return [
            'id' => (int) $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'country' => $this->country,
            'description' => strip_tags((string) $this->description),
        ];
    }

    public function searchableAs(): string
    {
        return 'companies';
    }

    public function cars(): HasMany
    {
        return $this->hasMany(Car::class);
    }

    public function shops(): BelongsToMany
    {
        return $this->belongsToMany(Shop::class, 'company_shops');
    }

    public function links(): HasMany
    {
        return $this->hasMany(Link::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(Image::class, 'company_id');
    }

    public function representations(): HasMany
    {
        return $this->hasMany(Representation::class);
    }
}
