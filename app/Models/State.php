<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

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

    public function repairShops(): HasMany
    {
        return $this->hasMany(RepairShop::class);
    }

    public function shops(): HasMany
    {
        return $this->hasMany(Shop::class);
    }
}
