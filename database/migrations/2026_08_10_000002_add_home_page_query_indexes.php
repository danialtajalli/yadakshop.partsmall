<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('images', function (Blueprint $table): void {
            $table->index(['shop_id', 'type'], 'images_shop_id_type_index');
            $table->index(['company_id', 'type'], 'images_company_id_type_index');
            $table->index(['repair_shop_id', 'type'], 'images_repair_shop_id_type_index');
        });

        Schema::table('shops', function (Blueprint $table): void {
            $table->index(['order', 'name', 'id'], 'shops_order_name_id_index');
        });

        Schema::table('parts', function (Blueprint $table): void {
            $table->index('name', 'parts_name_index');
        });

        Schema::table('pages', function (Blueprint $table): void {
            $table->index('slug', 'pages_slug_index');
        });
    }

    public function down(): void
    {
        Schema::table('pages', function (Blueprint $table): void {
            $table->dropIndex('pages_slug_index');
        });

        Schema::table('parts', function (Blueprint $table): void {
            $table->dropIndex('parts_name_index');
        });

        Schema::table('shops', function (Blueprint $table): void {
            $table->dropIndex('shops_order_name_id_index');
        });

        Schema::table('images', function (Blueprint $table): void {
            $table->dropIndex('images_repair_shop_id_type_index');
            $table->dropIndex('images_company_id_type_index');
            $table->dropIndex('images_shop_id_type_index');
        });
    }
};
