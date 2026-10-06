<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shops', function (Blueprint $table): void {
            $table->unsignedInteger('home_order')->nullable()->after('order');
            $table->index(['confirmed', 'home_order', 'name', 'id'], 'shops_home_order_index');
        });

        DB::table('shops')->update([
            'home_order' => DB::raw(DB::connection()->getQueryGrammar()->wrap('order')),
        ]);
    }

    public function down(): void
    {
        Schema::table('shops', function (Blueprint $table): void {
            $table->dropIndex('shops_home_order_index');
            $table->dropColumn('home_order');
        });
    }
};
