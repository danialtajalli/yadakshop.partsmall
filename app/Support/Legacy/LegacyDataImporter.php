<?php

namespace App\Support\Legacy;

use App\Enums\ImageType;
use App\Enums\LinkType;
use App\Enums\PhoneType;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class LegacyDataImporter
{
    /** @var set<int> */
    private array $partsCategoryIds = [];

    /** @var array<int, string> */
    private array $partsCategoryNames = [];

    /** @var array<string, int> */
    private array $modelCategoryIds = [];

    public function __construct(
        private readonly LegacyInsertParser $parser,
    ) {}

    public function import(): void
    {
        $this->withoutForeignKeyChecks(function (): void {
            $this->seedStates();
            $this->seedCities();
            $this->seedCompanies();
            $this->seedModelCategories();
            $this->seedModels();
            $this->seedCars();
            $this->seedPartsCategories();
            $this->seedRepairCategories();
            $this->seedWages();
            $this->seedParts();
            $this->seedRepairShops();
            $this->seedShops();
            $this->seedComments();
            $this->seedPages();
            $this->seedUsers();
        });
    }

    private function withoutForeignKeyChecks(callable $callback): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement('SET FOREIGN_KEY_CHECKS=0');
        } else {
            DB::statement('PRAGMA foreign_keys = OFF');
        }

        try {
            $this->truncateLegacyTables();
            $callback();
        } finally {
            if (DB::getDriverName() === 'mysql') {
                DB::statement('SET FOREIGN_KEY_CHECKS=1');
            } else {
                DB::statement('PRAGMA foreign_keys = ON');
            }
        }
    }

    private function truncateLegacyTables(): void
    {
        $tables = [
            'car_model',
            'repair_category_repair_shop',
            'part_repair_category',
            'part_wage',
            'parts_category_shop',
            'part_shop',
            'phones',
            'links',
            'images',
            'comments',
            'parts',
            'parts_categories',
            'repair_categories',
            'wages',
            'cars',
            'model_categories',
            'models',
            'companies',
            'repair_shops',
            'shops',
            'pages',
            'cities',
            'states',
            'users',
        ];

        foreach ($tables as $table) {
            DB::table($table)->truncate();
        }
    }

    private function seedStates(): void
    {
        $rows = array_map(fn (array $row): array => [
            'id' => (int) $row['id'],
            'name' => $row['name'],
            'slug' => $row['slug'],
            'tel_prefix' => $row['tel_prefix'],
            'created_at' => now(),
            'updated_at' => now(),
        ], $this->parser->rows('state'));

        DB::table('states')->insert($rows);
    }

    private function seedCities(): void
    {
        $rows = array_map(fn (array $row): array => [
            'id' => (int) $row['id'],
            'name' => $row['name'],
            'slug' => $row['slug'] ?: Str::slug($row['name']),
            'state_id' => (int) $row['state'],
            'created_at' => now(),
            'updated_at' => now(),
        ], $this->parser->rows('city'));

        DB::table('cities')->insert($rows);
    }

    private function seedCompanies(): void
    {
        $links = [];
        $images = [];

        foreach ($this->parser->rows('company') as $row) {
            DB::table('companies')->insert([
                'id' => (int) $row['id'],
                'name' => $row['name'],
                'description' => $row['des'] ?: null,
                'slug' => $row['latin'],
                'country' => $row['country'] ?: null,
                'wage_strike' => (float) ($row['wage_strike'] ?? 1),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            if ($this->filled($row['telegram'] ?? null)) {
                $links[] = $this->linkRow(
                    name: $row['telegram'],
                    type: LinkType::Telegram->value,
                    companyId: (int) $row['id'],
                );
            }

            if ($this->filled($row['image'] ?? null)) {
                $images[] = $this->imageRow(
                    type: ImageType::Logo->value,
                    path: $row['image'],
                    companyId: (int) $row['id'],
                );
            }
        }

        if ($links !== []) {
            DB::table('links')->insert($links);
        }

        if ($images !== []) {
            DB::table('images')->insert($images);
        }
    }

    private function seedModelCategories(): void
    {
        $legacyCats = [];

        foreach ($this->parser->rows('model') as $row) {
            $legacyCats[trim((string) ($row['cat'] ?? ''))] = true;
        }

        $now = now();
        $rows = [];

        foreach (array_keys($legacyCats) as $legacyCat) {
            $id = count($rows) + 1;

            $rows[] = [
                'id' => $id,
                'name' => $legacyCat,
                'slug' => ModelCategoryDefinitions::slugFor($legacyCat),
                'created_at' => $now,
                'updated_at' => $now,
            ];

            $this->modelCategoryIds[$legacyCat] = $id;
        }

        DB::table('model_categories')->insert($rows);
    }

    private function seedModels(): void
    {
        $rows = array_map(fn (array $row): array => [
            'id' => (int) $row['id'],
            'name' => $row['name'],
            'description' => $row['des'] ?: null,
            'slug' => $row['latin'],
            'category_id' => $this->modelCategoryIds[trim((string) ($row['cat'] ?? ''))] ?? null,
            'created_at' => now(),
            'updated_at' => now(),
        ], $this->parser->rows('model'));

        DB::table('models')->insert($rows);
    }

    private function seedCars(): void
    {
        $pivotRows = [];

        foreach ($this->parser->rows('car') as $row) {
            DB::table('cars')->insert([
                'id' => (int) $row['id'],
                'name' => $row['name'],
                'description' => $row['des'] ?: null,
                'slug' => $row['latin'],
                'company_id' => (int) $row['cat'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            foreach ($this->splitIds($row['custom1'] ?? null) as $modelId) {
                $pivotRows[] = [
                    'car_id' => (int) $row['id'],
                    'model_id' => $modelId,
                ];
            }
        }

        if ($pivotRows !== []) {
            DB::table('car_model')->insert($pivotRows);
        }
    }

    private function seedPartsCategories(): void
    {
        foreach ($this->parser->rows('categorypart') as $row) {
            $id = (int) $row['id'];
            $this->partsCategoryIds[$id] = $id;
            $this->partsCategoryNames[$id] = $row['name'];

            DB::table('parts_categories')->insert([
                'id' => $id,
                'name' => $row['name'],
                'created_at' => $this->timestamp($row['create_at'] ?? null),
                'updated_at' => $this->timestamp($row['create_at'] ?? null),
            ]);
        }
    }

    private function seedRepairCategories(): void
    {
        $rows = array_map(fn (array $row): array => [
            'id' => (int) $row['id'],
            'name' => $row['name'],
            'created_at' => $this->timestamp($row['create_at'] ?? null),
            'updated_at' => $this->timestamp($row['create_at'] ?? null),
        ], $this->parser->rows('type_repair'));

        DB::table('repair_categories')->insert($rows);
    }

    private function seedWages(): void
    {
        $rows = array_map(fn (array $row): array => [
            'id' => (int) $row['id'],
            'name' => $row['name'],
            'variable' => (float) $row['variable'],
            'coefficient' => (float) ($row['coefficient'] ?: 1),
            'created_at' => now(),
            'updated_at' => now(),
        ], $this->parser->rows('wage'));

        DB::table('wages')->insert($rows);
    }

    private function seedParts(): void
    {
        $repairPivot = [];
        $wagePivot = [];

        foreach ($this->parser->rows('part') as $row) {
            $partId = (int) $row['id'];
            $categoryId = (int) $row['categorypart_id'];
            $name = $this->requiredPartName($row, $categoryId);

            DB::table('parts')->insert([
                'id' => $partId,
                'name' => $name,
                'description' => $row['des'] ?: null,
                'category_description' => $row['cat'] ?: null,
                'slug' => $this->requiredSlug($row['latin'] ?? null, $name, 'part', $partId),
                'parts_category_id' => $categoryId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            foreach ($this->splitIds($row['type_repair_id'] ?? null) as $repairCategoryId) {
                $repairPivot[] = [
                    'part_id' => $partId,
                    'repair_category_id' => $repairCategoryId,
                ];
            }

            foreach ($this->splitIds($row['wage_id'] ?? null) as $wageId) {
                $wagePivot[] = [
                    'part_id' => $partId,
                    'wage_id' => $wageId,
                ];
            }
        }

        if ($repairPivot !== []) {
            DB::table('part_repair_category')->insert($repairPivot);
        }

        if ($wagePivot !== []) {
            DB::table('part_wage')->insert($wagePivot);
        }
    }

    private function seedRepairShops(): void
    {
        foreach ($this->parser->rows('repair_shop') as $row) {
            $repairShopId = (int) $row['id'];

            DB::table('repair_shops')->insert([
                'id' => $repairShopId,
                'name' => $row['name'],
                'slug' => $row['latin'],
                'responsible_person_name' => $row['name_person'] ?: null,
                'work_description' => $row['work_fields'] ?: null,
                'state_id' => $this->nullableInt($row['state'] ?? null),
                'address' => $row['address'] ?: null,
                'latitude' => $this->nullableDecimal($row['latitude'] ?? null),
                'longitude' => $this->nullableDecimal($row['longitude'] ?? null),
                'description' => $row['description'] ?: null,
                'created_at' => $this->timestamp($row['createdate'] ?? null),
                'updated_at' => $this->timestamp($row['createdate'] ?? null),
            ]);

            $this->insertContactChannels(
                repairShopId: $repairShopId,
                row: $row,
            );

            $this->syncRepairShopCategories($repairShopId, $row['type_repair_id'] ?? null);

            if ($this->filled($row['logo'] ?? null)) {
                DB::table('images')->insert($this->imageRow(
                    type: ImageType::Logo->value,
                    path: $row['logo'],
                    repairShopId: $repairShopId,
                ));
            }
        }
    }

    private function seedShops(): void
    {
        $partPivot = [];
        $categoryPivot = [];

        foreach ($this->parser->rows('shop') as $row) {
            $shopId = (int) $row['id'];

            DB::table('shops')->insert([
                'id' => $shopId,
                'name' => $row['name'],
                'secondary_name' => $row['under_name'] ?: null,
                'slug' => $this->requiredSlug($row['latin'] ?? null, $row['name'], 'shop', $shopId),
                'confirmed' => (bool) ($row['confirmed'] ?? false),
                'show_under_product' => (bool) ($row['show_under_product'] ?? false),
                'description' => $row['description'] ?: null,
                'person_responsible_name' => $row['managment'] ?: null,
                'person_responsible_email' => $row['email'] ?: null,
                'website_show' => $row['webname'] ?: null,
                'order' => (int) ($row['sort'] ?? 0),
                'latitude' => $this->nullableDecimal($row['latitude'] ?? null),
                'longitude' => $this->nullableDecimal($row['longitude'] ?? null),
                'state_id' => $this->nullableInt($row['state'] ?? null),
                'address' => $row['address'] ?: null,
                'open_time' => $this->timeValue($row['time_start'] ?? '09:00'),
                'close_time' => $this->timeValue($row['time_end'] ?? '18:00'),
                'open_time_friday' => $this->timeValue($row['time_start_friday'] ?? null),
                'close_time_friday' => $this->timeValue($row['time_end_friday'] ?? null),
                'open_time_thursday' => $this->timeValue($row['time_start_thursday'] ?? null),
                'close_time_thursday' => $this->timeValue($row['time_end_thursday'] ?? null),
                'off' => (bool) ($row['off'] ?? true),
                'created_at' => $this->timestamp($row['created_at'] ?? null),
                'updated_at' => $this->timestamp($row['created_at'] ?? null),
            ]);

            $this->insertContactChannels(shopId: $shopId, row: $row);

            foreach ($this->splitIds($row['part'] ?? null) as $partId) {
                $partPivot[] = [
                    'shop_id' => $shopId,
                    'part_id' => $partId,
                ];
            }

            foreach ($this->existingPartsCategoryIds($row['cat'] ?? null) as $categoryId) {
                $categoryPivot[] = [
                    'shop_id' => $shopId,
                    'parts_category_id' => $categoryId,
                ];
            }
        }

        if ($partPivot !== []) {
            DB::table('part_shop')->insert($partPivot);
        }

        if ($categoryPivot !== []) {
            DB::table('parts_category_shop')->insert($categoryPivot);
        }
    }

    private function seedComments(): void
    {
        $rows = array_map(fn (array $row): array => [
            'id' => (int) $row['id'],
            'fullname' => $row['fullname'] ?: null,
            'shop_id' => (int) $row['shop_id'],
            'mobile' => $row['mobile'] ?: null,
            'body' => $row['comment1'] ?: null,
            'rating' => $this->nullableInt($row['rating'] ?? null),
            'confirmed' => (bool) ($row['confirmed'] ?? false),
            'created_at' => $this->timestamp($row['created_at'] ?? null),
            'updated_at' => $this->timestamp($row['created_at'] ?? null),
        ], $this->parser->rows('comments'));

        if ($rows !== []) {
            DB::table('comments')->insert($rows);
        }
    }

    private function seedPages(): void
    {
        $rows = array_map(fn (array $row): array => [
            'id' => (int) $row['id'],
            'title' => $row['title'] ?: null,
            'slug' => $row['slug'] ?: null,
            'content' => $row['content'] ?: null,
            'created_at' => $this->timestamp($row['date'] ?? null),
            'updated_at' => $this->timestamp($row['date'] ?? null),
        ], $this->parser->rows('page'));

        if ($rows !== []) {
            DB::table('pages')->insert($rows);
        }
    }

    private function seedUsers(): void
    {
        foreach ($this->parser->rows('users') as $row) {
            $userId = (int) $row['id'];

            DB::table('users')->insert([
                'id' => $userId,
                'username' => $row['email'] ?: $row['name'],
                'topic' => $row['topic'] ?: null,
                'message' => $row['message'] ?: null,
                'created_at' => $this->timestamp($row['created_at'] ?? null),
                'updated_at' => $this->timestamp($row['created_at'] ?? null),
            ]);

            if ($this->filled($row['phone'] ?? null)) {
                DB::table('phones')->insert([
                    'shop_id' => null,
                    'repair_shop_id' => null,
                    'user_id' => $userId,
                    'phone_number' => $row['phone'],
                    'type' => PhoneType::Mobile->value,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    /**
     * @param  array<string, mixed>  $row
     */
    private function insertContactChannels(?int $shopId = null, ?int $repairShopId = null, array $row = []): void
    {
        $phones = [];
        $links = [];
        $now = now();

        $phoneMap = [
            ['tel1', PhoneType::Land],
            ['mob1', PhoneType::Mobile],
            ['mobile', PhoneType::Mobile],
            ['telephone', PhoneType::Land],
            ['telwhatsapp', PhoneType::Whatsapp],
            ['tel_whastapp', PhoneType::Whatsapp],
            ['tel_telegram', PhoneType::Telegram],
        ];

        foreach ($phoneMap as [$column, $type]) {
            if (! $this->filled($row[$column] ?? null)) {
                continue;
            }

            foreach ($this->splitPhoneNumbers((string) $row[$column]) as $number) {
                $phones[] = [
                    'shop_id' => $shopId,
                    'repair_shop_id' => $repairShopId,
                    'user_id' => null,
                    'phone_number' => $number,
                    'type' => $type->value,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        $messengerMap = [
            ['robika', PhoneType::Rubika],
            ['eta', PhoneType::Eita],
            ['soroush', PhoneType::Soroush],
            ['baleh', PhoneType::Ble],
            ['gap', PhoneType::Gap],
            ['igap', PhoneType::Igap],
        ];

        foreach ($messengerMap as [$column, $type]) {
            if ($this->filled($row[$column] ?? null)) {
                $phones[] = [
                    'shop_id' => $shopId,
                    'repair_shop_id' => $repairShopId,
                    'user_id' => null,
                    'phone_number' => (string) $row[$column],
                    'type' => $type->value,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        $linkMap = [
            ['weblink', LinkType::Website],
            ['website', LinkType::Website],
            ['wlink', LinkType::Whatsapp],
            ['whatsapp', LinkType::Whatsapp],
            ['telegram', LinkType::Telegram],
            ['id_telegram', LinkType::Telegram],
        ];

        foreach ($linkMap as [$column, $type]) {
            if ($this->filled($row[$column] ?? null)) {
                $links[] = $this->linkRow(
                    name: (string) $row[$column],
                    type: $type->value,
                    companyId: null,
                    repairShopId: $repairShopId,
                    shopId: $shopId,
                );
            }
        }

        if ($this->filled($row['instagram'] ?? null)) {
            $instagram = (string) $row['instagram'];
            $links[] = $this->linkRow(
                name: str_starts_with($instagram, 'http') ? $instagram : 'https://www.instagram.com/'.$instagram,
                type: LinkType::Instagram->value,
                companyId: null,
                repairShopId: $repairShopId,
                shopId: $shopId,
            );
        }

        if ($phones !== []) {
            DB::table('phones')->insert($phones);
        }

        if ($links !== []) {
            DB::table('links')->insert($links);
        }
    }

    private function syncRepairShopCategories(int $repairShopId, mixed $value): void
    {
        $rows = array_map(
            fn (int $repairCategoryId): array => [
                'repair_shop_id' => $repairShopId,
                'repair_category_id' => $repairCategoryId,
            ],
            $this->splitIds($value),
        );

        if ($rows !== []) {
            DB::table('repair_category_repair_shop')->insert($rows);
        }
    }

    /**
     * @return list<int>
     */
    private function existingPartsCategoryIds(mixed $value): array
    {
        return array_values(array_filter(
            $this->splitIds($value),
            fn (int $id): bool => isset($this->partsCategoryIds[$id]),
        ));
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

    /**
     * @return list<string>
     */
    private function splitPhoneNumbers(string $value): array
    {
        $parts = preg_split('/\s*,\s*/', $value) ?: [];

        return array_values(array_filter(array_map('trim', $parts)));
    }

    private function linkRow(
        string $name,
        string $type,
        ?int $companyId = null,
        ?int $repairShopId = null,
        ?int $shopId = null,
    ): array {
        $now = now();

        return [
            'name' => $name,
            'link_type' => $type,
            'company_id' => $companyId,
            'repair_shop_id' => $repairShopId,
            'shop_id' => $shopId,
            'created_at' => $now,
            'updated_at' => $now,
        ];
    }

    private function imageRow(
        string $type,
        string $path,
        ?int $companyId = null,
        ?int $repairShopId = null,
        ?int $shopId = null,
    ): array {
        $now = now();

        return [
            'type' => $type,
            'path' => $path,
            'company_id' => $companyId,
            'repair_shop_id' => $repairShopId,
            'shop_id' => $shopId,
            'created_at' => $now,
            'updated_at' => $now,
        ];
    }

    private function filled(mixed $value): bool
    {
        return $value !== null && trim((string) $value) !== '';
    }

    private function requiredSlug(?string $latin, string $name, string $prefix, int $id): string
    {
        if ($this->filled($latin)) {
            return (string) $latin;
        }

        $slug = Str::slug($name);

        return $slug !== '' ? $slug : "{$prefix}-{$id}";
    }

    /**
     * @param  array<string, mixed>  $row
     */
    private function requiredPartName(array $row, int $categoryId): string
    {
        if ($this->filled($row['name'] ?? null)) {
            return (string) $row['name'];
        }

        $categoryName = $this->partsCategoryNames[$categoryId] ?? null;

        if ($categoryName !== null) {
            return "{$categoryName} #{$row['id']}";
        }

        return "part-{$row['id']}";
    }

    private function nullableInt(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        return (int) $value;
    }

    private function nullableDecimal(mixed $value): ?float
    {
        if ($value === null || trim((string) $value) === '') {
            return null;
        }

        return (float) $value;
    }

    private function timeValue(mixed $value): ?string
    {
        if (! $this->filled($value)) {
            return null;
        }

        return substr((string) $value, 0, 5);
    }

    private function timestamp(mixed $value): Carbon
    {
        if ($this->filled($value)) {
            return Carbon::parse((string) $value);
        }

        return now();
    }
}
