<?php

namespace Database\Seeders;

use App\Enums\ImageType;
use App\Support\Legacy\LegacyInsertParser;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ShopLogoSeeder extends Seeder
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
        $existingShopIds = array_flip(DB::table('shops')->pluck('id')->all());
        $now = now();
        $rows = [];

        foreach ($parser->rows('shop') as $row) {
            $shopId = (int) $row['id'];
            $logo = trim((string) ($row['logo'] ?? ''));

            if ($logo === '' || ! isset($existingShopIds[$shopId])) {
                continue;
            }

            $rows[] = [
                'type' => ImageType::Logo->value,
                'path' => $logo,
                'company_id' => null,
                'repair_shop_id' => null,
                'shop_id' => $shopId,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        DB::table('images')
            ->where('type', ImageType::Logo->value)
            ->whereNotNull('shop_id')
            ->delete();

        foreach (array_chunk($rows, 100) as $chunk) {
            DB::table('images')->insert($chunk);
        }

        $this->command?->info('Seeded '.count($rows).' shop logo images from shop.logo.');
    }
}
