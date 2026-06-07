<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RepairShop extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'responsible_person_name',
        'work_description',
        'state_id',
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

    public function state(): BelongsTo
    {
        return $this->belongsTo(State::class);
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
        return $this->hasMany(Image::class);
    }

    public function repairCategories(): BelongsToMany
    {
        return $this->belongsToMany(RepairCategory::class, 'repair_category_repair_shop');
    }
}
