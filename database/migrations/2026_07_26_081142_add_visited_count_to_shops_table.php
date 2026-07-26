<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shops', function (Blueprint $table) {
            $table->unsignedInteger('visited_count')->nullable()->after('order');
        });

        DB::table('shops')->orderBy('id')->chunkById(100, function ($shops): void {
            foreach ($shops as $shop) {
                DB::table('shops')
                    ->where('id', $shop->id)
                    ->update(['visited_count' => random_int(2000, 2100)]);
            }
        });
    }

    public function down(): void
    {
        Schema::table('shops', function (Blueprint $table) {
            $table->dropColumn('visited_count');
        });
    }
};
