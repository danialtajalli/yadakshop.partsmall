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
            PhoneType::Mobile => $this->formatMobileLabel($digits, $raw),
            default => $raw,
        };
    }

    private function formatLandlineLabel(string $digits, string $fallback): string
    {
        if (strlen($digits) < 8) {
            return $fallback;
        }

        [$a, $b, $c, $d] = str_split(substr($digits, -8), 2);
        $local = "{$a} {$b} {$c}{$d}";
        $city = substr($digits, 0, -8);

        return $city === '' ? $local : "{$city} - {$local}";
    }

    private function formatMobileLabel(string $digits, string $fallback): string
    {
        // From the right: 2 + 2 + 3, remainder is the prefix (e.g. 0911).
        if (strlen($digits) < 7) {
            return $fallback;
        }

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
