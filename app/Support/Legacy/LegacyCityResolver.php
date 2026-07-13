<?php

namespace App\Support\Legacy;

use Illuminate\Support\Facades\DB;

class LegacyCityResolver
{
    /** @var array<int, int>|null */
    private static ?array $cityIds = null;

    public static function resolve(?int $legacyCityId, ?int $legacyStateId, ?string $address): ?int
    {
        if ($legacyCityId !== null && $legacyCityId > 0 && isset(self::knownCityIds()[$legacyCityId])) {
            return $legacyCityId;
        }

        if ($legacyStateId === null || $legacyStateId <= 0) {
            return null;
        }

        if (! isset(self::knownStateIds()[$legacyStateId])) {
            return null;
        }

        $cities = DB::table('cities')
            ->select(['id', 'name'])
            ->where('state_id', $legacyStateId)
            ->orderBy('name')
            ->get();

        if ($cities->isEmpty()) {
            return null;
        }

        $address = trim((string) $address);

        if ($address !== '') {
            foreach ($cities as $city) {
                if (str_contains($address, $city->name)) {
                    return (int) $city->id;
                }
            }
        }

        return (int) $cities->first()->id;
    }

    /**
     * @return array<int, int>
     */
    private static function knownCityIds(): array
    {
        if (self::$cityIds === null) {
            self::$cityIds = array_flip(DB::table('cities')->pluck('id')->all());
        }

        return self::$cityIds;
    }

    /**
     * @return array<int, int>
     */
    private static function knownStateIds(): array
    {
        static $stateIds = null;

        if ($stateIds === null) {
            $stateIds = array_flip(DB::table('states')->pluck('id')->all());
        }

        return $stateIds;
    }

    public static function resetCache(): void
    {
        self::$cityIds = null;
    }
}
