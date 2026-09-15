<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('phone_numbers', function (Blueprint $table) {
            $table->id();
            $table->string('phone_number');
            $table->string('country_code', 8)->default('+62');
            $table->string('normalized_number', 20)->unique();
            $table->unsignedSmallInteger('risk_score')->default(0);
            $table->enum('risk_level', ['low', 'caution', 'risky', 'high'])->default('low');
            $table->unsignedInteger('total_reports')->default(0);
            $table->unsignedInteger('total_reviews')->default(0);
            $table->unsignedInteger('total_tags')->default(0);
            $table->unsignedInteger('search_count')->default(0);
            $table->enum('status', ['active', 'hidden'])->default('active');
            $table->timestamps();

            $table->index(['risk_level', 'total_reports']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('phone_numbers');
    }
};