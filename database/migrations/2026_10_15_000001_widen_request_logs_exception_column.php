<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('request_logs', function (Blueprint $table) {
            $table->longText('exception')->nullable()->change();
        });
    }

    public function down(): void
    {
        $byteLength = DB::getDriverName() === 'sqlite'
            ? 'length(CAST(exception AS BLOB))'
            : 'OCTET_LENGTH(exception)';

        if (DB::table('request_logs')->whereRaw($byteLength.' > ?', [65535])->exists()) {
            throw new RuntimeException('Cannot narrow request_logs.exception to TEXT: existing logs exceed 65,535 bytes.');
        }

        Schema::table('request_logs', function (Blueprint $table) {
            $table->text('exception')->nullable()->change();
        });
    }
};
