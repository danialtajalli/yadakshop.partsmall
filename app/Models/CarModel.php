<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Laravel\Scout\Searchable;

class CarModel extends Model
{
    use Searchable;

    protected $table = 'models';

    protected $fillable = [
        'name',
        'description',
        'slug',
        'category_id',
    ];

    public function toSearchableArray(): array
    {
        $this->loadMissing(['cars.company', 'category']);

        return [
            'id' => (int) $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => strip_tags((string) $this->description),
            'category_id' => $this->category_id,
            'category_name' => $this->category?->name,
            'cars' => $this->cars->pluck('name')->all(),
            'companies' => $this->cars->pluck('company.name')->filter()->unique()->values()->all(),
        ];
    }

    public function searchableAs(): string
    {
        return 'car_models';
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ModelCategory::class, 'category_id');
    }

    public function cars(): BelongsToMany
    {
        return $this->belongsToMany(Car::class, 'car_model', 'model_id', 'car_id');
    }
}
