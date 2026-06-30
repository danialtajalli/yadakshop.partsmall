<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Laravel\Scout\Searchable;

class Part extends Model
{
    use Searchable;

    public function toSearchableArray(): array
    {
        $this->loadMissing('partsCategory');

        return [
            'id' => (int) $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'category_description' => strip_tags((string) $this->category_description),
            'parts_category_id' => $this->parts_category_id,
            'parts_category_name' => $this->partsCategory?->name,
        ];
    }

    public function searchableAs(): string
    {
        return 'parts';
    }

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
