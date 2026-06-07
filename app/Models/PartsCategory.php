<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PartsCategory extends Model
{
    protected $fillable = [
        'name',
    ];

    public function parts(): HasMany
    {
        return $this->hasMany(Part::class);
    }

    public function shops(): BelongsToMany
    {
        return $this->belongsToMany(Shop::class, 'parts_category_shop');
    }
}
