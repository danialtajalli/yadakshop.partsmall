<?php

namespace App\Support;

use __PHP_Incomplete_Class;
use Illuminate\Support\Facades\Cache;

class SafeCache
{
    public static function remember(string $key, int $ttl, callable $callback, ?callable $isValid = null): mixed
    {
        $cached = Cache::get($key);

        if (($cached !== null || Cache::has($key)) && self::isValid($cached, $isValid)) {
            return $cached;
        }

        Cache::forget($key);

        $value = $callback();
        Cache::put($key, $value, $ttl);

        return $value;
    }

    private static function isValid(mixed $value, ?callable $isValid): bool
    {
        if ($value instanceof __PHP_Incomplete_Class) {
            return false;
        }

        return $isValid === null || $isValid($value);
    }
}
