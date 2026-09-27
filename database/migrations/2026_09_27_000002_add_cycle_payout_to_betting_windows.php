<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('betting_windows')) {
            return;
        }

        Schema::table('betting_windows', function (Blueprint $table) {
            if (!Schema::hasColumn('betting_windows', 'first_card_matched')) {
                $table->boolean('first_card_matched')->nullable()->after('ended_at');
            }
            if (!Schema::hasColumn('betting_windows', 'payout_mode')) {
                $table->unsignedTinyInteger('payout_mode')->nullable()->after('first_card_matched');
            }
            if (!Schema::hasColumn('betting_windows', 'payout_locked')) {
                $table->boolean('payout_locked')->default(false)->after('payout_mode');
            }
            if (!Schema::hasColumn('betting_windows', 'payout_processed')) {
                $table->boolean('payout_processed')->default(false)->after('payout_locked');
            }
            if (!Schema::hasColumn('betting_windows', 'winning_side')) {
                $table->string('winning_side', 20)->nullable()->after('payout_processed');
            }
        });

        if (!Schema::hasColumn('game_rounds', 'payout_locked') || !Schema::hasColumn('game_rounds', 'payout_mode')) {
            return;
        }

        $lockedRounds = DB::table('game_rounds')
            ->where('payout_locked', 1)
            ->whereNotNull('payout_mode')
            ->get(['id', 'first_card_matched', 'payout_mode']);

        foreach ($lockedRounds as $round) {
            DB::table('betting_windows')
                ->where('game_round_id', $round->id)
                ->where('status', 'closed')
                ->whereNull('payout_mode')
                ->update([
                    'first_card_matched' => $round->first_card_matched,
                    'payout_mode' => $round->payout_mode,
                    'payout_locked' => true,
                ]);
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('betting_windows')) {
            return;
        }

        Schema::table('betting_windows', function (Blueprint $table) {
            foreach (['winning_side', 'payout_processed', 'payout_locked', 'payout_mode', 'first_card_matched'] as $column) {
                if (Schema::hasColumn('betting_windows', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
