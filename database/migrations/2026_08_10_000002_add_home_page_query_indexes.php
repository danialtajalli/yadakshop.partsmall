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
            $table->index('slug', 'shops_slug_index');
            $table->index(['show_under_product', 'order', 'name', 'id'], 'shops_product_visibility_order_index');
        });

        Schema::table('parts', function (Blueprint $table): void {
            $table->index('name', 'parts_name_index');
            $table->index('slug', 'parts_slug_index');
            $table->index(['parts_category_id', 'category_description'], 'parts_category_description_index');
        });

        Schema::table('pages', function (Blueprint $table): void {
            $table->index('slug', 'pages_slug_index');
        });

        Schema::table('companies', function (Blueprint $table): void {
            $table->index('slug', 'companies_slug_index');
        });

        Schema::table('cars', function (Blueprint $table): void {
            $table->index(['company_id', 'slug'], 'cars_company_id_slug_index');
            $table->index(['company_id', 'name'], 'cars_company_id_name_index');
        });

        Schema::table('models', function (Blueprint $table): void {
            $table->index('slug', 'models_slug_index');
            $table->index(['category_id', 'name'], 'models_category_id_name_index');
        });

        Schema::table('representations', function (Blueprint $table): void {
            $table->index('slug', 'representations_slug_index');
        });

        Schema::table('repair_shops', function (Blueprint $table): void {
            $table->index('slug', 'repair_shops_slug_index');
        });

        Schema::table('car_model', function (Blueprint $table): void {
            $table->index(['car_id', 'model_id'], 'car_model_car_id_model_id_index');
            $table->index(['model_id', 'car_id'], 'car_model_model_id_car_id_index');
        });

        Schema::table('company_shops', function (Blueprint $table): void {
            $table->index(['company_id', 'shop_id'], 'company_shops_company_id_shop_id_index');
            $table->index(['shop_id', 'company_id'], 'company_shops_shop_id_company_id_index');
        });

        Schema::table('part_shop', function (Blueprint $table): void {
            $table->index(['part_id', 'shop_id'], 'part_shop_part_id_shop_id_index');
            $table->index(['shop_id', 'part_id'], 'part_shop_shop_id_part_id_index');
        });

        Schema::table('part_repair_category', function (Blueprint $table): void {
            $table->index(['part_id', 'repair_category_id'], 'part_repair_category_part_id_category_id_index');
        });
    }

    public function down(): void
    {
        Schema::table('part_repair_category', function (Blueprint $table): void {
            $table->dropIndex('part_repair_category_part_id_category_id_index');
        });

        Schema::table('part_shop', function (Blueprint $table): void {
            $table->dropIndex('part_shop_shop_id_part_id_index');
            $table->dropIndex('part_shop_part_id_shop_id_index');
        });

        Schema::table('company_shops', function (Blueprint $table): void {
            $table->dropIndex('company_shops_shop_id_company_id_index');
            $table->dropIndex('company_shops_company_id_shop_id_index');
        });

        Schema::table('car_model', function (Blueprint $table): void {
            $table->dropIndex('car_model_model_id_car_id_index');
            $table->dropIndex('car_model_car_id_model_id_index');
        });

        Schema::table('repair_shops', function (Blueprint $table): void {
            $table->dropIndex('repair_shops_slug_index');
        });

        Schema::table('representations', function (Blueprint $table): void {
            $table->dropIndex('representations_slug_index');
        });

        Schema::table('models', function (Blueprint $table): void {
            $table->dropIndex('models_category_id_name_index');
            $table->dropIndex('models_slug_index');
        });

        Schema::table('cars', function (Blueprint $table): void {
            $table->dropIndex('cars_company_id_name_index');
            $table->dropIndex('cars_company_id_slug_index');
        });

        Schema::table('companies', function (Blueprint $table): void {
            $table->dropIndex('companies_slug_index');
        });

        Schema::table('pages', function (Blueprint $table): void {
            $table->dropIndex('pages_slug_index');
        });

        Schema::table('parts', function (Blueprint $table): void {
            $table->dropIndex('parts_category_description_index');
            $table->dropIndex('parts_slug_index');
            $table->dropIndex('parts_name_index');
        });

        Schema::table('shops', function (Blueprint $table): void {
            $table->dropIndex('shops_product_visibility_order_index');
            $table->dropIndex('shops_slug_index');
            $table->dropIndex('shops_order_name_id_index');
        });

        Schema::table('images', function (Blueprint $table): void {
            $table->dropIndex('images_repair_shop_id_type_index');
            $table->dropIndex('images_company_id_type_index');
            $table->dropIndex('images_shop_id_type_index');
        });
    }
};
