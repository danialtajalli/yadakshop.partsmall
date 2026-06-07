<?php

namespace App\Models;

use App\Enums\LinkType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Link extends Model
{
    protected $fillable = [
        'name',
        'link_type',
        'company_id',
        'repair_shop_id',
        'shop_id',
    ];

    protected function casts(): array
    {
        return [
            'link_type' => LinkType::class,
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function repairShop(): BelongsTo
    {
        return $this->belongsTo(RepairShop::class);
    }

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }
}
