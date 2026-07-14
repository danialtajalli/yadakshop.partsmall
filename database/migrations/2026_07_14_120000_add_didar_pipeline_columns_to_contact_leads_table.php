<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contact_leads', function (Blueprint $table) {
            $table->string('didar_owner_id')->nullable()->after('didar_product_id');
            $table->string('didar_pipeline_id')->nullable()->after('didar_owner_id');
            $table->string('didar_pipeline_stage_id')->nullable()->after('didar_pipeline_id');
        });
    }

    public function down(): void
    {
        Schema::table('contact_leads', function (Blueprint $table) {
            $table->dropColumn([
                'didar_owner_id',
                'didar_pipeline_id',
                'didar_pipeline_stage_id',
            ]);
        });
    }
};
