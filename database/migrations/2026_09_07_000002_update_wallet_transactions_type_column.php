<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Change type column from ENUM to VARCHAR(50) so it supports bet_cancelled_refunded and any future types
        DB::statement("ALTER TABLE `wallet_transactions` MODIFY COLUMN `type` VARCHAR(50) NOT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE `wallet_transactions` MODIFY COLUMN `type` ENUM('points_added','bet_deducted','bet_refunded','winning_points_added','withdrawal','manual_credit','manual_debit','adjustment') NOT NULL");
    }
};
