<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class CarModel extends Model
{
    protected $table = 'models';

    protected $fillable = [
        'name',
        'description',
        'slug',
    ];

    public function cars(): BelongsToMany
    {
        return $this->belongsToMany(Car::class, 'car_model', 'model_id', 'car_id');
    }
}
