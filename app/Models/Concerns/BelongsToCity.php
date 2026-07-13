<?php

namespace App\Models\Concerns;

use App\Models\City;
use App\Models\State;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;

trait BelongsToCity
{
    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    public function state(): HasOneThrough
    {
        return $this->hasOneThrough(
            State::class,
            City::class,
            'id',
            'id',
            'city_id',
            'state_id',
        );
    }
}
