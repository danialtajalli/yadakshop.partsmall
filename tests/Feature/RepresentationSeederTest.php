<?php

namespace Tests\Feature;

use App\Models\Representation;
use App\Support\Legacy\LegacyInsertParser;
use Database\Seeders\RepresentationSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

class RepresentationSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_representation_seeder_imports_rows_from_sql_dump(): void
    {
        if (! is_file(base_path('partsmall_db.sql'))) {
            $this->markTestSkipped('partsmall_db.sql is not available.');
        }

        $parser = new LegacyInsertParser(file_get_contents(base_path('partsmall_db.sql')) ?: '');
        $now = now();

        DB::table('states')->insert(array_map(fn (array $row): array => [
            'id' => (int) $row['id'],
            'name' => $row['name'],
            'slug' => $row['slug'],
            'tel_prefix' => $row['tel_prefix'],
            'created_at' => $now,
            'updated_at' => $now,
        ], $parser->rows('state')));

        DB::table('cities')->insert(array_map(fn (array $row): array => [
            'id' => (int) $row['id'],
            'name' => $row['name'],
            'slug' => $row['slug'] ?: Str::slug($row['name']),
            'state_id' => (int) $row['state'],
            'created_at' => $now,
            'updated_at' => $now,
        ], $parser->rows('city')));

        foreach ($parser->rows('company') as $row) {
            DB::table('companies')->insert([
                'id' => (int) $row['id'],
                'name' => $row['name'],
                'description' => $row['des'] ?: null,
                'slug' => $row['latin'],
                'country' => $row['country'] ?: null,
                'wage_strike' => (float) ($row['wage_strike'] ?? 1),
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        $this->seed(RepresentationSeeder::class);

        $this->assertSame(223, Representation::count());
        $this->assertDatabaseHas('representations', [
            'slug' => 'asanmotor',
            'name' => 'شركت آسان موتور',
            'company_id' => 1,
            'city_id' => 301,
        ]);
    }
}
