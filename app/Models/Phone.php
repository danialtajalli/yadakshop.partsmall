<?php

namespace App\Models;

use App\Enums\PhoneType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Phone extends Model
{
    protected $fillable = [
        'shop_id',
        'repair_shop_id',
        'user_id',
        'phone_number',
        'type',
    ];

    protected function casts(): array
    {
        return [
            'type' => PhoneType::class,
        ];
    }

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    public function repairShop(): BelongsTo
    {
        return $this->belongsTo(RepairShop::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
