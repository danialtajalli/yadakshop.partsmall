<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Comment extends Model
{
    protected $fillable = [
        'fullname',
        'shop_id',
        'mobile',
        'body',
        'rating',
        'confirmed',
    ];

    protected function casts(): array
    {
        return [
            'rating' => 'integer',
            'confirmed' => 'boolean',
        ];
    }

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }
}
