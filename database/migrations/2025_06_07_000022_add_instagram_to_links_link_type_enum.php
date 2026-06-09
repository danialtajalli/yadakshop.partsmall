<?php

use App\Enums\LinkType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('links', function (Blueprint $table) {
            $table->enum('link_type', array_column(LinkType::cases(), 'value'))->change();
        });
    }

    public function down(): void
    {
        Schema::table('links', function (Blueprint $table) {
            $table->enum('link_type', array_filter(
                array_column(LinkType::cases(), 'value'),
                fn (string $value) => $value !== LinkType::Instagram->value,
            ))->change();
        });
    }
};
