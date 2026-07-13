<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('shops', 'city_id')) {
            Schema::table('shops', function (Blueprint $table): void {
                $table->foreignId('city_id')->nullable()->after('longitude')->constrained()->nullOnDelete();
            });
        }

        if (! Schema::hasColumn('repair_shops', 'city_id')) {
            Schema::table('repair_shops', function (Blueprint $table): void {
                $table->foreignId('city_id')->nullable()->after('work_description')->constrained()->nullOnDelete();
            });
        }

        if (Schema::hasColumn('shops', 'state_id')) {
            $this->backfillCityIds('shops');
        }

        if (Schema::hasColumn('repair_shops', 'state_id')) {
            $this->backfillCityIds('repair_shops');
        }

        if (Schema::hasColumn('representations', 'state_id')) {
            $this->backfillRepresentationCityIds();
        }

        if (Schema::hasColumn('shops', 'state_id')) {
            Schema::table('shops', function (Blueprint $table): void {
                $table->dropConstrainedForeignId('state_id');
            });
        }

        if (Schema::hasColumn('repair_shops', 'state_id')) {
            Schema::table('repair_shops', function (Blueprint $table): void {
                $table->dropConstrainedForeignId('state_id');
            });
        }

        if (Schema::hasColumn('representations', 'state_id')) {
            Schema::table('representations', function (Blueprint $table): void {
                $table->dropConstrainedForeignId('state_id');
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasColumn('shops', 'state_id')) {
            Schema::table('shops', function (Blueprint $table): void {
                $table->foreignId('state_id')->nullable()->after('longitude')->constrained()->nullOnDelete();
            });
        }

        if (! Schema::hasColumn('repair_shops', 'state_id')) {
            Schema::table('repair_shops', function (Blueprint $table): void {
                $table->foreignId('state_id')->nullable()->after('work_description')->constrained()->nullOnDelete();
            });
        }

        if (! Schema::hasColumn('representations', 'state_id')) {
            Schema::table('representations', function (Blueprint $table): void {
                $table->foreignId('state_id')->nullable()->after('instagram')->constrained()->nullOnDelete();
            });
        }

        if (Schema::hasColumn('shops', 'city_id')) {
            $this->restoreStateIds('shops');
        }

        if (Schema::hasColumn('repair_shops', 'city_id')) {
            $this->restoreStateIds('repair_shops');
        }

        if (Schema::hasColumn('representations', 'city_id')) {
            $this->restoreRepresentationStateIds();
        }

        if (Schema::hasColumn('shops', 'city_id') && ! Schema::hasColumn('shops', 'state_id')) {
            Schema::table('shops', function (Blueprint $table): void {
                $table->dropConstrainedForeignId('city_id');
            });
        }

        if (Schema::hasColumn('repair_shops', 'city_id') && ! Schema::hasColumn('repair_shops', 'state_id')) {
            Schema::table('repair_shops', function (Blueprint $table): void {
                $table->dropConstrainedForeignId('city_id');
            });
        }
    }

    private function backfillCityIds(string $table): void
    {
        $rows = DB::table($table)
            ->select(['id', 'state_id', 'address'])
            ->whereNotNull('state_id')
            ->get();

        foreach ($rows as $row) {
            $cityId = $this->resolveCityId((int) $row->state_id, $row->address);

            if ($cityId !== null) {
                DB::table($table)->where('id', $row->id)->update(['city_id' => $cityId]);
            }
        }
    }

    private function backfillRepresentationCityIds(): void
    {
        $rows = DB::table('representations')
            ->select(['id', 'state_id', 'city_id', 'address'])
            ->get();

        foreach ($rows as $row) {
            if ($row->city_id !== null) {
                continue;
            }

            $cityId = $this->resolveCityId(
                $row->state_id !== null ? (int) $row->state_id : null,
                $row->address,
            );

            if ($cityId !== null) {
                DB::table('representations')->where('id', $row->id)->update(['city_id' => $cityId]);
            }
        }
    }

    private function restoreStateIds(string $table): void
    {
        $rows = DB::table($table)
            ->select(['id', 'city_id'])
            ->whereNotNull('city_id')
            ->get();

        foreach ($rows as $row) {
            $stateId = DB::table('cities')->where('id', $row->city_id)->value('state_id');

            if ($stateId !== null) {
                DB::table($table)->where('id', $row->id)->update(['state_id' => $stateId]);
            }
        }
    }

    private function restoreRepresentationStateIds(): void
    {
        $rows = DB::table('representations')
            ->select(['id', 'city_id'])
            ->whereNotNull('city_id')
            ->get();

        foreach ($rows as $row) {
            $stateId = DB::table('cities')->where('id', $row->city_id)->value('state_id');

            if ($stateId !== null) {
                DB::table('representations')->where('id', $row->id)->update(['state_id' => $stateId]);
            }
        }
    }

    private function resolveCityId(?int $stateId, ?string $address): ?int
    {
        if ($stateId === null) {
            return null;
        }

        $cities = DB::table('cities')
            ->select(['id', 'name'])
            ->where('state_id', $stateId)
            ->orderBy('name')
            ->get();

        if ($cities->isEmpty()) {
            return null;
        }

        if ($address) {
            foreach ($cities as $city) {
                if (str_contains($address, $city->name)) {
                    return (int) $city->id;
                }
            }
        }

        return (int) $cities->first()->id;
    }
};
