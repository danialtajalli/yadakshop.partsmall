<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Wage extends Model
{
    protected $fillable = [
        'name',
        'variable',
        'coefficient',
    ];

    protected function casts(): array
    {
        return [
            'variable' => 'float',
            'coefficient' => 'float',
        ];
    }

    public function parts(): BelongsToMany
    {
        return $this->belongsToMany(Part::class, 'part_wage');
    }
}
