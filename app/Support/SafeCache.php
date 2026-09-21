<?php

namespace App\Support;

use __PHP_Incomplete_Class;
use Illuminate\Contracts\Cache\Repository;
use Illuminate\Support\Facades\Cache;

class SafeCache
{
    /**
     * @param  list<string>  $tags
     */
    public static function remember(
        string $key,
        int $ttl,
        callable $callback,
        ?callable $isValid = null,
        array $tags = [],
    ): mixed {
        $repository = self::repository($tags);
        $resolvedKey = self::resolveKey($key, $tags);
        $cached = $repository->get($resolvedKey);

        if (($cached !== null || $repository->has($resolvedKey)) && self::isValid($cached, $isValid)) {
            return $cached;
        }

        $repository->forget($resolvedKey);

        $value = $callback();
        $repository->put($resolvedKey, $value, $ttl);

        return $value;
    }

    /**
     * @param  list<string>  $tags
     */
    public static function flushTags(array $tags): void
    {
        $tags = array_values(array_unique(array_filter($tags)));

        if ($tags === []) {
            return;
        }

        if (self::supportsTags()) {
            Cache::tags($tags)->flush();

            return;
        }

        foreach ($tags as $tag) {
            $versionKey = self::versionKey($tag);
            Cache::forever($versionKey, ((int) Cache::get($versionKey, 1)) + 1);
        }
    }

    /**
     * @param  list<string>  $tags
     */
    private static function repository(array $tags): Repository
    {
        if ($tags !== [] && self::supportsTags()) {
            return Cache::tags($tags);
        }

        return Cache::driver();
    }

    /**
     * @param  list<string>  $tags
     */
    private static function resolveKey(string $key, array $tags): string
    {
        if ($tags === [] || self::supportsTags()) {
            return $key;
        }

        $versions = collect($tags)
            ->map(fn (string $tag): string => $tag.':'.(int) Cache::get(self::versionKey($tag), 1))
            ->implode('|');

        return "{$key}|{$versions}";
    }

    private static function versionKey(string $tag): string
    {
        return "content-cache-version:{$tag}";
    }

    private static function supportsTags(): bool
    {
        return Cache::supportsTags();
    }

    private static function isValid(mixed $value, ?callable $isValid): bool
    {
        if ($value instanceof __PHP_Incomplete_Class) {
            return false;
        }

        return $isValid === null || $isValid($value);
    }
}
