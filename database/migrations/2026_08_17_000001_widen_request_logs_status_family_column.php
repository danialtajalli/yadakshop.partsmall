<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('request_logs')) {
            return;
        }

        match (DB::getDriverName()) {
            'mysql', 'mariadb' => DB::statement('ALTER TABLE request_logs MODIFY status_family SMALLINT UNSIGNED NULL'),
            'pgsql' => DB::statement('ALTER TABLE request_logs ALTER COLUMN status_family TYPE SMALLINT'),
            default => null,
        };
    }

    public function down(): void
    {
        if (! Schema::hasTable('request_logs')) {
            return;
        }

        match (DB::getDriverName()) {
            'mysql', 'mariadb' => DB::statement('ALTER TABLE request_logs MODIFY status_family TINYINT UNSIGNED NULL'),
            'pgsql' => DB::statement('ALTER TABLE request_logs ALTER COLUMN status_family TYPE SMALLINT'),
            default => null,
        };
    }
};
