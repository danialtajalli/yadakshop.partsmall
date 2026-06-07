<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('car_model', function (Blueprint $table) {
            $table->foreignId('car_id')->constrained()->cascadeOnDelete();
            $table->foreignId('model_id')->constrained()->cascadeOnDelete();

            $table->primary(['car_id', 'model_id']);
        });

        Schema::create('repair_category_repair_shop', function (Blueprint $table) {
            $table->foreignId('repair_shop_id')->constrained()->cascadeOnDelete();
            $table->foreignId('repair_category_id')->constrained()->cascadeOnDelete();

            $table->primary(['repair_shop_id', 'repair_category_id'], 'repair_shop_category_primary');
        });

        Schema::create('part_repair_category', function (Blueprint $table) {
            $table->foreignId('part_id')->constrained()->cascadeOnDelete();
            $table->foreignId('repair_category_id')->constrained()->cascadeOnDelete();

            $table->primary(['part_id', 'repair_category_id']);
        });

        Schema::create('part_wage', function (Blueprint $table) {
            $table->foreignId('part_id')->constrained()->cascadeOnDelete();
            $table->foreignId('wage_id')->constrained()->cascadeOnDelete();

            $table->primary(['part_id', 'wage_id']);
        });

        Schema::create('parts_category_shop', function (Blueprint $table) {
            $table->foreignId('shop_id')->constrained()->cascadeOnDelete();
            $table->foreignId('parts_category_id')->constrained()->cascadeOnDelete();

            $table->primary(['shop_id', 'parts_category_id'], 'parts_category_shop_primary');
        });

        Schema::create('part_shop', function (Blueprint $table) {
            $table->foreignId('shop_id')->constrained()->cascadeOnDelete();
            $table->foreignId('part_id')->constrained()->cascadeOnDelete();

            $table->primary(['shop_id', 'part_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('part_shop');
        Schema::dropIfExists('parts_category_shop');
        Schema::dropIfExists('part_wage');
        Schema::dropIfExists('part_repair_category');
        Schema::dropIfExists('repair_category_repair_shop');
        Schema::dropIfExists('car_model');
    }
};
