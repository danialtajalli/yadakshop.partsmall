<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('shops')->update(['confirmed' => true]);
    }

    public function down(): void
    {
        // Previous confirmation states cannot be recovered safely.
    }
};
