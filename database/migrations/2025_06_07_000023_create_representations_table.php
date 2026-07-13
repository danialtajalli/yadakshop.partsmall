<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('representations', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug');
            $table->string('responsible_person_name')->nullable();
            $table->string('work_fields')->nullable();
            $table->string('mobile', 20)->nullable();
            $table->string('telephone', 20)->nullable();
            $table->foreignId('company_id')->nullable()->constrained()->nullOnDelete();
            $table->string('service_type')->nullable();
            $table->string('website')->nullable();
            $table->string('website_name')->nullable();
            $table->string('whatsapp')->nullable();
            $table->string('whatsapp_phone')->nullable();
            $table->string('telegram')->nullable();
            $table->string('telegram_phone')->nullable();
            $table->string('instagram')->nullable();
            $table->foreignId('city_id')->nullable()->constrained()->nullOnDelete();
            $table->string('address')->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->longText('description')->nullable();
            $table->string('logo')->nullable();
            $table->string('nearby_railway')->nullable();
            $table->string('nearby_bus')->nullable();
            $table->string('nearby_railway_name')->nullable();
            $table->string('nearby_bus_name')->nullable();
            $table->float('nearby_railway_distance')->default(0);
            $table->float('nearby_bus_distance')->default(0);
            $table->boolean('show_under_product')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('representations');
    }
};
