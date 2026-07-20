<?php

namespace App\Support;

use Illuminate\Support\Collection;

class CarModelSort
{
    /**
     * @param  Collection<int, array{slug: string, name: string, url: string, category_slug?: string}>  $models
     * @return list<array{slug: string, name: string, url: string, category_slug?: string}>
     */
    public static function prioritize(Collection $models): array
    {
        return $models
            ->sort(function (array $left, array $right): int {
                $leftBucket = self::bucket($left['category_slug'] ?? '');
                $rightBucket = self::bucket($right['category_slug'] ?? '');

                if ($leftBucket !== $rightBucket) {
                    return $leftBucket <=> $rightBucket;
                }

                return match ($leftBucket) {
                    0, 1 => self::numericValue($right) <=> self::numericValue($left),
                    2 => self::numericValue($left) <=> self::numericValue($right),
                    default => strnatcasecmp($left['name'], $right['name']),
                };
            })
            ->values()
            ->all();
    }

    public static function bucketForCategory(string $categorySlug): int
    {
        return self::bucket($categorySlug);
    }

    private static function bucket(string $categorySlug): int
    {
        return match ($categorySlug) {
            'year-miladi' => 0,
            'year-shamsi', 'year-fa' => 1,
            'cc' => 2,
            default => 3,
        };
    }

    /**
     * @param  array{slug: string, name: string}  $model
     */
    private static function numericValue(array $model): int
    {
        if (is_numeric($model['slug'])) {
            return (int) $model['slug'];
        }

        if (preg_match('/(\d+)/', $model['name'], $matches) === 1) {
            return (int) $matches[1];
        }

        if (preg_match('/(\d+)/', $model['slug'], $matches) === 1) {
            return (int) $matches[1];
        }

        return 0;
    }
}
