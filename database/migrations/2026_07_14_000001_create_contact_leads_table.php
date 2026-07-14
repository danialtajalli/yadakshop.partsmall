<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contact_leads', function (Blueprint $table) {
            $table->id();
            $table->string('first_name', 100);
            $table->string('last_name', 100);
            $table->string('phone', 15);
            $table->text('message');
            $table->string('status', 32)->default('pending');
            $table->string('pipeline', 64);
            $table->string('didar_person_id')->nullable();
            $table->string('didar_deal_id')->nullable();
            $table->string('didar_product_id')->nullable();
            $table->text('failure_reason')->nullable();
            $table->timestamps();

            $table->index('phone');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contact_leads');
    }
};
