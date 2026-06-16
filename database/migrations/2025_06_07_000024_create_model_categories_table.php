<?php

use App\Support\Legacy\ModelCategoryDefinitions;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('model_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->timestamps();
        });

        $now = now();
        $rows = [];

        foreach (ModelCategoryDefinitions::legacyCats() as $legacyCat) {
            $rows[] = [
                'name' => $legacyCat,
                'slug' => ModelCategoryDefinitions::slugFor($legacyCat),
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        DB::table('model_categories')->insert($rows);
    }

    public function down(): void
    {
        Schema::dropIfExists('model_categories');
    }
};
