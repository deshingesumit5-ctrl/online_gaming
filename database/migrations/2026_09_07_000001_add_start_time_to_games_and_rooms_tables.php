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
        Schema::table('games', function (Blueprint $table) {
            if (!Schema::hasColumn('games', 'start_time')) {
                $table->timestamp('start_time')->nullable()->after('status');
            }
        });

        Schema::table('rooms', function (Blueprint $table) {
            if (!Schema::hasColumn('rooms', 'start_time')) {
                $table->timestamp('start_time')->nullable()->after('status');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('games', function (Blueprint $table) {
            if (Schema::hasColumn('games', 'start_time')) {
                $table->dropColumn('start_time');
            }
        });

        Schema::table('rooms', function (Blueprint $table) {
            if (Schema::hasColumn('rooms', 'start_time')) {
                $table->dropColumn('start_time');
            }
        });
    }
};
