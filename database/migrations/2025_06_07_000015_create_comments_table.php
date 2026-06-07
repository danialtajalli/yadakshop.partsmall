<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('comments', function (Blueprint $table) {
            $table->id();
            $table->string('fullname')->nullable();
            $table->foreignId('shop_id')->constrained()->cascadeOnDelete();
            $table->string('mobile')->nullable();
            $table->text('body')->nullable();
            $table->integer('rating')->nullable();
            $table->boolean('confirmed')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('comments');
    }
};
