<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PartsCategoryShop extends Model
{
    protected $table = 'parts_category_shop';

    protected $fillable = [
        'shop_id',
        'parts_category_id',
    ];

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }
}
