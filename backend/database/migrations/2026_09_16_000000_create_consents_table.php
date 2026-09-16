<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('consents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('consent_version', 32);
            $table->string('scope')->default('contact_contribution');
            $table->timestamps();

            $table->unique(['user_id', 'consent_version']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('consents');
    }
};
