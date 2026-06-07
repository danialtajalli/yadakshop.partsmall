<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Car extends Model
{
    protected $fillable = [
        'name',
        'description',
        'slug',
        'company_id',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function models(): BelongsToMany
    {
        return $this->belongsToMany(CarModel::class, 'car_model', 'car_id', 'model_id');
    }
}
