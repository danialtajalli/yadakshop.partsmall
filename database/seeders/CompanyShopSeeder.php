<?php

namespace Database\Seeders;

use App\Support\Legacy\LegacyInsertParser;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CompanyShopSeeder extends Seeder
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
        $shopIds = array_flip(DB::table('shops')->pluck('id')->all());
        $rows = [];
        $seen = [];

        foreach ($parser->rows('shop') as $shopRow) {
            $shopId = (int) ($shopRow['id'] ?? 0);

            if ($shopId === 0 || ! isset($shopIds[$shopId])) {
                continue;
            }

            foreach ($this->splitIds($shopRow['cat'] ?? null) as $companyId) {
                if (! isset($companyIds[$companyId])) {
                    continue;
                }

                $key = "{$companyId}:{$shopId}";

                if (isset($seen[$key])) {
                    continue;
                }

                $seen[$key] = true;
                $rows[] = [
                    'company_id' => $companyId,
                    'shop_id' => $shopId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        DB::table('company_shops')->truncate();

        foreach (array_chunk($rows, 500) as $chunk) {
            DB::table('company_shops')->insert($chunk);
        }

        $this->command?->info('Seeded '.count($rows).' company_shops rows from shop.cat.');
    }

    /**
     * @return list<int>
     */
    private function splitIds(mixed $value): array
    {
        if ($value === null || $value === '') {
            return [];
        }

        return array_values(array_unique(array_filter(array_map(
            static fn (string $part): int => (int) $part,
            preg_split('/\s*,\s*/', (string) $value) ?: [],
        ), static fn (int $id): bool => $id > 0)));
    }
}
