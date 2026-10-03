<?php

namespace App\Models;

use App\Enums\PhoneType;
use App\Support\EnglishDigits;
use App\Support\IranAreaCodes;
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
     * Text shown to visitors.
     * Landlines: "021 - 91 55 6162"
     * Mobiles: "0911 111 11 11"
     * Messengers keep the stored dialable number.
     */
    public function displayLabel(): string
    {
        $raw = (string) $this->phone_number;
        $digits = preg_replace('/\D+/', '', EnglishDigits::convert($raw)) ?? '';

        return match ($this->type) {
            PhoneType::Land => $this->formatLandlineLabel($digits, $raw),
            PhoneType::Mobile => strlen($digits) < 8
                ? $raw
                : $this->formatMobileLabel($digits),
            default => $raw,
        };
    }

    private function formatLandlineLabel(string $digits, string $fallback): string
    {
        $city = IranAreaCodes::matchPrefix($digits);
        $local = $city === null ? $digits : substr($digits, strlen($city));

        // Local part must be a full 8-digit subscriber number.
        if (strlen($local) !== 8) {
            return $fallback;
        }

        [$a, $b, $c, $d] = str_split($local, 2);
        $formatted = "{$a} {$b} {$c}{$d}";

        return $city === null ? $formatted : "{$city} - {$formatted}";
    }

    private function formatMobileLabel(string $digits): string
    {
        // From the right: 2 + 2 + 3, remainder is the prefix (e.g. 0911).
        $pair1 = substr($digits, -2);
        $pair2 = substr($digits, -4, 2);
        $triple = substr($digits, -7, 3);
        $prefix = substr($digits, 0, -7);

        return $prefix === ''
            ? "{$triple} {$pair2} {$pair1}"
            : "{$prefix} {$triple} {$pair2} {$pair1}";
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
