<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shops', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('secondary_name')->nullable();
            $table->string('slug');
            $table->boolean('confirmed')->default(false);
            $table->boolean('show_under_product')->default(false);
            $table->text('description')->nullable();
            $table->string('person_responsible_name')->nullable();
            $table->string('person_responsible_email')->nullable();
            $table->string('website_show')->nullable();
            $table->unsignedInteger('order')->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->foreignId('state_id')->nullable()->constrained()->nullOnDelete();
            $table->string('address')->nullable();
            $table->time('open_time')->default('09:00');
            $table->time('close_time')->default('18:00');
            $table->time('open_time_friday')->nullable();
            $table->time('close_time_friday')->nullable();
            $table->time('open_time_thursday')->nullable();
            $table->time('close_time_thursday')->nullable();
            $table->boolean('off')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shops');
    }
};
