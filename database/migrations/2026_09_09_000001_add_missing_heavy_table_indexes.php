<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('comments', function (Blueprint $table): void {
            $table->index(['shop_id', 'confirmed'], 'comments_shop_id_confirmed_index');
            $table->index('confirmed', 'comments_confirmed_index');
        });

        Schema::table('parts_category_shop', function (Blueprint $table): void {
            $table->index(['shop_id', 'parts_category_id'], 'parts_category_shop_shop_id_category_id_index');
            $table->index(['parts_category_id', 'shop_id'], 'parts_category_shop_category_id_shop_id_index');
        });

        Schema::table('repair_category_repair_shop', function (Blueprint $table): void {
            $table->index(['repair_shop_id', 'repair_category_id'], 'repair_category_repair_shop_shop_id_category_id_index');
            $table->index(['repair_category_id', 'repair_shop_id'], 'repair_category_repair_shop_category_id_shop_id_index');
        });

        Schema::table('part_repair_category', function (Blueprint $table): void {
            $table->index(['repair_category_id', 'part_id'], 'part_repair_category_category_id_part_id_index');
        });

        Schema::table('links', function (Blueprint $table): void {
            $table->index(['company_id', 'link_type'], 'links_company_id_link_type_index');
            $table->index(['shop_id', 'link_type'], 'links_shop_id_link_type_index');
        });

        Schema::table('cities', function (Blueprint $table): void {
            $table->index(['state_id', 'name'], 'cities_state_id_name_index');
        });

        Schema::table('shops', function (Blueprint $table): void {
            $table->index('confirmed', 'shops_confirmed_index');
        });

        Schema::table('representations', function (Blueprint $table): void {
            $table->index(['show_under_product', 'name'], 'representations_product_visibility_name_index');
            $table->index(['city_id', 'name'], 'representations_city_id_name_index');
        });

        Schema::table('repair_shops', function (Blueprint $table): void {
            $table->index(['city_id', 'name'], 'repair_shops_city_id_name_index');
        });

        Schema::table('contact_leads', function (Blueprint $table): void {
            $table->index('pipeline', 'contact_leads_pipeline_index');
            $table->index(['status', 'created_at'], 'contact_leads_status_created_at_index');
        });
    }

    public function down(): void
    {
        Schema::table('contact_leads', function (Blueprint $table): void {
            $table->dropIndex('contact_leads_status_created_at_index');
            $table->dropIndex('contact_leads_pipeline_index');
        });

        Schema::table('repair_shops', function (Blueprint $table): void {
            $table->dropIndex('repair_shops_city_id_name_index');
        });

        Schema::table('representations', function (Blueprint $table): void {
            $table->dropIndex('representations_city_id_name_index');
            $table->dropIndex('representations_product_visibility_name_index');
        });

        Schema::table('shops', function (Blueprint $table): void {
            $table->dropIndex('shops_confirmed_index');
        });

        Schema::table('cities', function (Blueprint $table): void {
            $table->dropIndex('cities_state_id_name_index');
        });

        Schema::table('links', function (Blueprint $table): void {
            $table->dropIndex('links_shop_id_link_type_index');
            $table->dropIndex('links_company_id_link_type_index');
        });

        Schema::table('part_repair_category', function (Blueprint $table): void {
            $table->dropIndex('part_repair_category_category_id_part_id_index');
        });

        Schema::table('repair_category_repair_shop', function (Blueprint $table): void {
            $table->dropIndex('repair_category_repair_shop_category_id_shop_id_index');
            $table->dropIndex('repair_category_repair_shop_shop_id_category_id_index');
        });

        Schema::table('parts_category_shop', function (Blueprint $table): void {
            $table->dropIndex('parts_category_shop_category_id_shop_id_index');
            $table->dropIndex('parts_category_shop_shop_id_category_id_index');
        });

        Schema::table('comments', function (Blueprint $table): void {
            $table->dropIndex('comments_confirmed_index');
            $table->dropIndex('comments_shop_id_confirmed_index');
        });
    }
};
