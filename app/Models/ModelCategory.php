<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ModelCategory extends Model
{
    protected $fillable = [
        'name',
        'slug',
    ];

    public function models(): HasMany
    {
        return $this->hasMany(CarModel::class, 'category_id');
    }
}
