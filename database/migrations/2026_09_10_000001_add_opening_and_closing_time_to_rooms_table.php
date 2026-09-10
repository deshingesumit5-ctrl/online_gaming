<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('rooms', function (Blueprint $table) {
            if (!Schema::hasColumn('rooms', 'opening_time')) {
                $table->string('opening_time')->nullable()->default('11:15 AM')->after('status');
            }
            if (!Schema::hasColumn('rooms', 'closing_time')) {
                $table->string('closing_time')->nullable()->default('10:00 PM')->after('opening_time');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rooms', function (Blueprint $table) {
            if (Schema::hasColumn('rooms', 'opening_time')) {
                $table->dropColumn('opening_time');
            }
            if (Schema::hasColumn('rooms', 'closing_time')) {
                $table->dropColumn('closing_time');
            }
        });
    }
};
