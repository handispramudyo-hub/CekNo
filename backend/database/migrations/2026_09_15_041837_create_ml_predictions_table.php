<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ml_predictions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('phone_number_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('review_id')->nullable()->constrained()->nullOnDelete();
            $table->string('model_type', 32);
            $table->string('model_version', 32)->default('1.0.0');
            $table->json('input_features')->nullable();
            $table->json('prediction')->nullable();
            $table->float('confidence')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ml_predictions');
    }
};