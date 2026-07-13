<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class State extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'tel_prefix',
    ];

    public function cities(): HasMany
    {
        return $this->hasMany(City::class);
    }

    public function shops(): HasManyThrough
    {
        return $this->hasManyThrough(Shop::class, City::class, 'state_id', 'city_id');
    }

    public function repairShops(): HasManyThrough
    {
        return $this->hasManyThrough(RepairShop::class, City::class, 'state_id', 'city_id');
    }

    public function representations(): HasManyThrough
    {
        return $this->hasManyThrough(Representation::class, City::class, 'state_id', 'city_id');
    }
}
