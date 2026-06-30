<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Laravel\Scout\Searchable;

class Car extends Model
{
    use Searchable;

    protected $fillable = [
        'name',
        'description',
        'slug',
        'company_id',
    ];

    public function toSearchableArray(): array
    {
        $this->loadMissing(['company', 'models']);

        return [
            'id' => (int) $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'company_id' => $this->company_id,
            'company_name' => $this->company?->name,
            'models' => $this->models->pluck('name')->all(),
        ];
    }

    public function searchableAs(): string
    {
        return 'cars';
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function models(): BelongsToMany
    {
        return $this->belongsToMany(CarModel::class, 'car_model', 'car_id', 'model_id');
    }
}
