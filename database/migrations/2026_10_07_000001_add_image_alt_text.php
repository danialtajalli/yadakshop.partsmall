<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('images', function (Blueprint $table) {
            $table->string('alt', 255)->nullable();
        });

        Schema::table('representations', function (Blueprint $table) {
            $table->string('logo_alt', 255)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('representations', function (Blueprint $table) {
            $table->dropColumn('logo_alt');
        });

        Schema::table('images', function (Blueprint $table) {
            $table->dropColumn('alt');
        });
    }
};
