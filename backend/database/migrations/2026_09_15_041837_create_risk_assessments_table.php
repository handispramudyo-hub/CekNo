<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('risk_assessments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('phone_number_id')->constrained()->cascadeOnDelete();
            $table->string('model_version', 32)->default('1.0.0');
            $table->float('xgboost_probability')->nullable();
            $table->float('indobert_probability')->nullable();
            $table->float('community_score')->nullable();
            $table->float('rule_score')->nullable();
            $table->unsignedSmallInteger('final_score')->default(0);
            $table->enum('risk_level', ['low', 'caution', 'risky', 'high'])->default('low');
            $table->string('explanation')->nullable();
            $table->text('factors')->nullable();
            $table->timestamps();

            $table->index(['phone_number_id', 'model_version']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('risk_assessments');
    }
};