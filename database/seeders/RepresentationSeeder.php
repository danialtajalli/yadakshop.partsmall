<?php

namespace Database\Seeders;

use App\Support\Legacy\LegacyInsertParser;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class RepresentationSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $sqlPath = base_path('partsmall_db.sql');

        if (! is_file($sqlPath)) {
            $this->command?->error('Missing partsmall_db.sql in the project root.');

            return;
        }

        $parser = new LegacyInsertParser(file_get_contents($sqlPath) ?: '');
        $companyIds = array_flip(DB::table('companies')->pluck('id')->all());
        $stateIds = array_flip(DB::table('states')->pluck('id')->all());
        $cityIds = array_flip(DB::table('cities')->pluck('id')->all());
        $rows = [];

        foreach ($parser->rows('representation') as $legacyRow) {
            $rows[] = $this->mapRow($legacyRow, $companyIds, $stateIds, $cityIds);
        }

        DB::table('representations')->truncate();

        foreach (array_chunk($rows, 100) as $chunk) {
            DB::table('representations')->insert($chunk);
        }

        $this->command?->info('Seeded '.count($rows).' representations from partsmall_db.sql.');
    }

    /**
     * @param  array<string, mixed>  $row
     * @param  array<int, int>  $companyIds
     * @param  array<int, int>  $stateIds
     * @param  array<int, int>  $cityIds
     * @return array<string, mixed>
     */
    private function mapRow(array $row, array $companyIds, array $stateIds, array $cityIds): array
    {
        $legacyStateId = (int) ($row['state'] ?? 0);
        $legacyCityId = (int) ($row['city'] ?? 0);
        $companyId = $this->resolveCompanyId((string) ($row['company_id'] ?? ''), $companyIds);
        $createdAt = $this->parseTimestamp($row['createdate'] ?? null);

        return [
            'id' => (int) $row['id'],
            'name' => (string) $row['name'],
            'slug' => (string) $row['latin'],
            'responsible_person_name' => $this->nullableString($row['name_person'] ?? null),
            'work_fields' => $this->nullableString($row['work_fields'] ?? null),
            'mobile' => $this->nullableString($row['mobile'] ?? null),
            'telephone' => $this->nullableString($row['telephone'] ?? null),
            'company_id' => $companyId,
            'service_type' => $this->nullableString($row['service_type'] ?? null),
            'website' => $this->nullableString($row['website'] ?? null),
            'website_name' => $this->nullableString($row['webname'] ?? null),
            'whatsapp' => $this->nullableString($row['whatsapp'] ?? null),
            'whatsapp_phone' => $this->nullableString($row['tel_whastapp'] ?? null),
            'telegram' => $this->nullableString($row['telegram'] ?? null),
            'telegram_phone' => $this->nullableString($row['tel_telegram'] ?? null),
            'instagram' => $this->nullableString($row['instagram'] ?? null),
            'state_id' => isset($stateIds[$legacyStateId]) ? $legacyStateId : null,
            'city_id' => isset($cityIds[$legacyCityId]) ? $legacyCityId : null,
            'address' => $this->nullableString($row['address'] ?? null),
            'latitude' => $this->nullableCoordinate($row['latitude'] ?? null),
            'longitude' => $this->nullableCoordinate($row['longitude'] ?? null),
            'description' => $this->nullableString($row['description'] ?? null),
            'logo' => $this->nullableString($row['logo'] ?? null),
            'nearby_railway' => $this->nullableString($row['nearby_railway'] ?? null),
            'nearby_bus' => $this->nullableString($row['nearby_bus'] ?? null),
            'nearby_railway_name' => $this->nullableString($row['nearby_railway_name'] ?? null),
            'nearby_bus_name' => $this->nullableString($row['nearby_bus_name'] ?? null),
            'nearby_railway_distance' => (float) ($row['nearby_railway_distance'] ?? 0),
            'nearby_bus_distance' => (float) ($row['nearby_bus_distance'] ?? 0),
            'show_under_product' => (bool) ($row['show_under_product'] ?? true),
            'created_at' => $createdAt,
            'updated_at' => $createdAt,
        ];
    }

    /**
     * @param  array<int, int>  $companyIds
     */
    private function resolveCompanyId(string $value, array $companyIds): ?int
    {
        foreach (preg_split('/\s*,\s*/', trim($value)) ?: [] as $part) {
            $id = (int) $part;

            if ($id > 0 && isset($companyIds[$id])) {
                return $id;
            }
        }

        return null;
    }

    private function nullableString(mixed $value): ?string
    {
        $string = trim((string) $value);

        return $string === '' ? null : $string;
    }

    private function nullableCoordinate(mixed $value): ?string
    {
        $string = trim((string) $value);

        if ($string === '' || ! is_numeric($string)) {
            return null;
        }

        return $string;
    }

    private function parseTimestamp(mixed $value): Carbon
    {
        $string = trim((string) $value);

        if ($string === '') {
            return now();
        }

        try {
            return Carbon::parse($string);
        } catch (\Throwable) {
            return now();
        }
    }
}
