<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('request_logs', function (Blueprint $table) {
            $table->id();
            $table->string('log_type', 64)->default('incoming_request')->index();
            $table->timestamp('occurred_at')->index();
            $table->string('method', 16);
            $table->text('url');
            $table->string('path', 2048);
            $table->string('route_name')->nullable()->index();
            $table->string('route_action')->nullable();
            $table->unsignedSmallInteger('status_code')->nullable()->index();
            $table->unsignedSmallInteger('status_family')->nullable()->index();
            $table->boolean('is_reportable_status')->default(false)->index();
            $table->unsignedInteger('duration_ms')->nullable();
            $table->foreignId('user_id')->nullable()->index();
            $table->ipAddress('ip')->nullable();
            $table->text('user_agent')->nullable();
            $table->text('referer')->nullable();
            $table->text('exception')->nullable();
            $table->json('query')->nullable();
            $table->timestamps();

            $table->index(['status_family', 'occurred_at']);
            $table->index(['is_reportable_status', 'occurred_at']);
            $table->index(['log_type', 'occurred_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('request_logs');
    }
};
