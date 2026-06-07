<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class RepairCategory extends Model
{
    protected $fillable = [
        'name',
    ];

    public function repairShops(): BelongsToMany
    {
        return $this->belongsToMany(RepairShop::class, 'repair_category_repair_shop');
    }

    public function parts(): BelongsToMany
    {
        return $this->belongsToMany(Part::class, 'part_repair_category');
    }
}
