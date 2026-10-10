<?php

namespace Tests\Feature;

use App\Models\Company;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ContentCacheReuseTest extends TestCase
{
    use RefreshDatabase;

    public function test_repeated_catalog_requests_use_plain_cached_rows_until_content_changes(): void
    {
        Cache::setDefaultDriver('database');
        $company = Company::query()->create(['name' => 'Example', 'slug' => 'example', 'country' => 'Example']);
        $builds = 0;
        DB::listen(function ($query) use (&$builds): void {
            if (str_starts_with(strtolower($query->sql), 'select')
                && str_contains($query->sql, 'from "companies"')
                && str_contains($query->sql, 'as "cars_count"')) {
                $builds++;
            }
        });

        $this->get(route('companies.index'))->assertOk()->assertSee('Example');
        $this->assertSame(1, $builds);
        $this->get(route('companies.index'))->assertOk()->assertSee('Example');
        $this->get(route('companies.index'))->assertOk()->assertSee('Example');
        $this->assertSame(1, $builds);

        $raw = DB::table('cache')->where('key', 'like', '%catalog:companies-index:v2%')->value('value');
        $payload = unserialize($raw, ['allowed_classes' => false]);
        $this->assertIsArray($payload);
        $this->assertSame('Example', $payload[0]['name']);

        $company->update(['name' => 'Updated']);
        $this->get(route('companies.index'))->assertOk()->assertSee('Updated');
        $this->assertSame(2, $builds);
    }
}
