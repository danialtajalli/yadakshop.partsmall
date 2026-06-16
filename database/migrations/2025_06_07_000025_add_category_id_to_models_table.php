<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('models', function (Blueprint $table) {
            $table->foreignId('category_id')
                ->nullable()
                ->after('slug')
                ->constrained('model_categories')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('models', function (Blueprint $table) {
            $table->dropConstrainedForeignId('category_id');
        });
    }
};
