<?php

namespace Database\Seeders;

use App\Support\Legacy\LegacyDataImporter;
use App\Support\Legacy\LegacyInsertParser;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PartsmallLegacySeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $sqlPath = base_path('partsmall_db.sql');

        if (! is_file($sqlPath)) {
            $this->command?->error('Missing partsmall_db.sql in the project root.');

            return;
        }

        $importer = new LegacyDataImporter(
            new LegacyInsertParser(file_get_contents($sqlPath) ?: ''),
        );

        $importer->import();

        $this->command?->info('Legacy data imported from partsmall_db.sql.');
    }
}
