<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ml_models', function (Blueprint $table) {
            $table->id();
            $table->string('algorithm', 32);
            $table->string('model_version', 32);
            $table->string('dataset_version', 32)->default('1.0.0');
            $table->timestamp('training_date')->nullable();
            $table->json('metrics')->nullable();
            $table->enum('status', ['experiment', 'active', 'retired'])->default('experiment');
            $table->string('model_path')->nullable();
            $table->unsignedInteger('storage_size_kb')->default(0);
            $table->timestamps();

            $table->unique(['algorithm', 'model_version']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ml_models');
    }
};