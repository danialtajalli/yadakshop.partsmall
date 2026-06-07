<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Part extends Model
{
    protected $fillable = [
        'name',
        'description',
        'category_description',
        'slug',
        'parts_category_id',
    ];

    public function partsCategory(): BelongsTo
    {
        return $this->belongsTo(PartsCategory::class);
    }

    public function repairCategories(): BelongsToMany
    {
        return $this->belongsToMany(RepairCategory::class, 'part_repair_category');
    }

    public function wages(): BelongsToMany
    {
        return $this->belongsToMany(Wage::class, 'part_wage');
    }

    public function shops(): BelongsToMany
    {
        return $this->belongsToMany(Shop::class, 'part_shop');
    }
}
