<?php

namespace App\Models;

use App\Enums\PhoneType;
use App\Support\EnglishDigits;
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

    /**
     * Text shown to visitors. Landlines are grouped as "021 - 91 55 6162";
     * other types keep the stored dialable number.
     */
    public function displayLabel(): string
    {
        $raw = (string) $this->phone_number;

        if ($this->type !== PhoneType::Land) {
            return $raw;
        }

        $digits = preg_replace('/\D+/', '', EnglishDigits::convert($raw)) ?? '';

        if (strlen($digits) < 8) {
            return $raw;
        }

        [$a, $b, $c, $d] = str_split(substr($digits, -8), 2);
        $local = "{$a} {$b} {$c}{$d}";
        $city = substr($digits, 0, -8);

        return $city === '' ? $local : "{$city} - {$local}";
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
