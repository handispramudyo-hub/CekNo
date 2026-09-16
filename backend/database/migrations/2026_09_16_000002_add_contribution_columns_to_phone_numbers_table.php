<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('phone_numbers', function (Blueprint $table) {
            $table->unsignedInteger('total_contributions')->default(0)->after('risk_level');
            $table->unsignedInteger('contributor_count')->default(0)->after('total_contributions');
        });
    }

    public function down(): void
    {
        Schema::table('phone_numbers', function (Blueprint $table) {
            $table->dropColumn(['total_contributions', 'contributor_count']);
        });
    }
};