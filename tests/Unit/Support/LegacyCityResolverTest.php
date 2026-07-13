<?php

namespace Tests\Unit\Support;

use App\Support\Legacy\LegacyCityResolver;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class LegacyCityResolverTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_uses_legacy_city_id_when_present(): void
    {
        $stateId = DB::table('states')->insertGetId([
            'name' => 'تهران',
            'slug' => 'tehran',
            'tel_prefix' => '021',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $cityId = DB::table('cities')->insertGetId([
            'name' => 'تهران',
            'slug' => 'tehran-city',
            'state_id' => $stateId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        LegacyCityResolver::resetCache();

        $this->assertSame(
            $cityId,
            LegacyCityResolver::resolve($cityId, $stateId, 'آدرس بدون نام شهر'),
        );
    }

    public function test_it_falls_back_to_address_matching_within_state(): void
    {
        $stateId = DB::table('states')->insertGetId([
            'name' => 'اصفهان',
            'slug' => 'isfahan',
            'tel_prefix' => '031',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('cities')->insert([
            [
                'name' => 'اصفهان',
                'slug' => 'isfahan-city',
                'state_id' => $stateId,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'شاهین‌شهر',
                'slug' => 'shahinshahr',
                'state_id' => $stateId,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        LegacyCityResolver::resetCache();

        $resolvedId = LegacyCityResolver::resolve(null, $stateId, 'اصفهان، خیابان چهارباغ');

        $this->assertSame(
            DB::table('cities')->where('slug', 'isfahan-city')->value('id'),
            $resolvedId,
        );
    }
}
