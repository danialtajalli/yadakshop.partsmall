<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Laravel\Scout\Searchable;

class RepairShop extends Model
{
    use BelongsToCity;
    use Searchable;

    protected $fillable = [
        'name',
        'slug',
        'responsible_person_name',
        'work_description',
        'city_id',
        'address',
        'latitude',
        'longitude',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'latitude' => 'decimal:8',
            'longitude' => 'decimal:8',
        ];
    }

    public function toSearchableArray(): array
    {
        $this->loadMissing(['repairCategories', 'city.state']);

        return [
            'id' => (int) $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'responsible_person_name' => $this->responsible_person_name,
            'work_description' => strip_tags((string) $this->work_description),
            'description' => strip_tags((string) $this->description),
            'address' => $this->address,
            'state_name' => $this->city?->state?->name,
            'city_name' => $this->city?->name,
            'repair_categories' => $this->repairCategories->pluck('name')->all(),
        ];
    }

    public function searchableAs(): string
    {
        return 'repair_shops';
    }

    public function phones(): HasMany
    {
        return $this->hasMany(Phone::class);
    }

    public function links(): HasMany
    {
        return $this->hasMany(Link::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(Image::class, 'repair_shop_id');
    }

    public function repairCategories(): BelongsToMany
    {
        return $this->belongsToMany(RepairCategory::class, 'repair_category_repair_shop');
    }

    public function profileUrl(): string
    {
        return route('repair-shop.profile', [
            'id' => $this->id,
            'slug' => $this->slug,
        ]);
    }
}
