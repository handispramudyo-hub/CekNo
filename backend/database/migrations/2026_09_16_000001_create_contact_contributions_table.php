<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contact_contributions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('phone_number_id')->constrained()->cascadeOnDelete();
            $table->string('label');
            $table->string('label_normalized');
            $table->string('category')->default('general');
            $table->string('consent_version', 32)->default('1.0');
            $table->string('status')->default('pending');
            $table->timestamps();

            $table->unique(['user_id', 'phone_number_id', 'label_normalized'], 'cc_user_number_label_unique');
            $table->index('status');
            $table->index(['phone_number_id', 'status'], 'cc_phone_status_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contact_contributions');
    }
};
