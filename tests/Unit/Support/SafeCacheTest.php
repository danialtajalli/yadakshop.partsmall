<?php

namespace Tests\Unit\Support;

use __PHP_Incomplete_Class;
use App\Models\Company;
use App\Models\Page;
use App\Support\ContentCacheInvalidator;
use App\Support\ContentCacheTag;
use App\Support\SafeCache;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class SafeCacheTest extends TestCase
{
    use RefreshDatabase;

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

    public function test_flushing_tags_forces_rebuild_on_next_remember(): void
    {
        $builds = 0;

        $build = function () use (&$builds): array {
            $builds++;

            return ['build' => $builds];
        };

        $first = SafeCache::remember('pages:navigation:v1', 60, $build, fn (mixed $value): bool => is_array($value), [
            ContentCacheTag::PAGES,
        ]);
        $second = SafeCache::remember('pages:navigation:v1', 60, $build, fn (mixed $value): bool => is_array($value), [
            ContentCacheTag::PAGES,
        ]);

        $this->assertSame(['build' => 1], $first);
        $this->assertSame(['build' => 1], $second);
        $this->assertSame(1, $builds);

        SafeCache::flushTags([ContentCacheTag::PAGES]);

        $third = SafeCache::remember('pages:navigation:v1', 60, $build, fn (mixed $value): bool => is_array($value), [
            ContentCacheTag::PAGES,
        ]);

        $this->assertSame(['build' => 2], $third);
        $this->assertSame(2, $builds);
    }

    public function test_saving_page_invalidates_pages_cache_tag(): void
    {
        $builds = 0;

        SafeCache::remember('pages:navigation:v1', 60, function () use (&$builds): array {
            $builds++;

            return ['build' => $builds];
        }, fn (mixed $value): bool => is_array($value), [ContentCacheTag::PAGES]);

        $this->assertSame(1, $builds);

        Page::query()->create([
            'title' => 'درباره ما',
            'slug' => 'about',
            'content' => 'متن',
        ]);

        SafeCache::remember('pages:navigation:v1', 60, function () use (&$builds): array {
            $builds++;

            return ['build' => $builds];
        }, fn (mixed $value): bool => is_array($value), [ContentCacheTag::PAGES]);

        $this->assertSame(2, $builds);
    }

    public function test_company_maps_to_home_and_catalog_tags(): void
    {
        $company = new Company;

        $this->assertSame(
            [ContentCacheTag::HOME, ContentCacheTag::CATALOG],
            ContentCacheInvalidator::tagsFor($company),
        );
    }
}
