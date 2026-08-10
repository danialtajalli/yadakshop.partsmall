<?php

namespace Tests\Unit\Support;

use __PHP_Incomplete_Class;
use App\Support\SafeCache;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class SafeCacheTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Cache::flush();
    }

    public function test_it_returns_valid_cached_values(): void
    {
        Cache::put('safe-cache:test', ['cached' => true], 60);

        $value = SafeCache::remember(
            'safe-cache:test',
            60,
            fn (): array => ['rebuilt' => true],
            fn (mixed $value): bool => is_array($value),
        );

        $this->assertSame(['cached' => true], $value);
    }

    public function test_it_rebuilds_invalid_cached_values(): void
    {
        Cache::put('safe-cache:test', 'not-an-array', 60);

        $value = SafeCache::remember(
            'safe-cache:test',
            60,
            fn (): array => ['rebuilt' => true],
            fn (mixed $value): bool => is_array($value),
        );

        $this->assertSame(['rebuilt' => true], $value);
        $this->assertSame(['rebuilt' => true], Cache::get('safe-cache:test'));
    }

    public function test_it_rebuilds_incomplete_class_cached_values(): void
    {
        $incomplete = @unserialize('O:17:"MissingCacheClass":0:{}');

        $this->assertInstanceOf(__PHP_Incomplete_Class::class, $incomplete);

        Cache::put('safe-cache:test', $incomplete, 60);

        $value = SafeCache::remember(
            'safe-cache:test',
            60,
            fn (): array => ['rebuilt' => true],
            fn (mixed $value): bool => is_array($value),
        );

        $this->assertSame(['rebuilt' => true], $value);
    }
}
