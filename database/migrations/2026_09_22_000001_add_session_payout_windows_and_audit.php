<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('game_rounds', function (Blueprint $table) {
            if (!Schema::hasColumn('game_rounds', 'payout_mode')) {
                $table->unsignedTinyInteger('payout_mode')->nullable()->after('winning_side');
            }
            if (!Schema::hasColumn('game_rounds', 'first_card_matched')) {
                $table->boolean('first_card_matched')->nullable()->after('payout_mode');
            }
            if (!Schema::hasColumn('game_rounds', 'payout_locked')) {
                $table->boolean('payout_locked')->default(false)->after('first_card_matched');
            }
        });

        if (!Schema::hasTable('betting_windows')) {
            Schema::create('betting_windows', function (Blueprint $table) {
                $table->id();
                $table->foreignId('game_round_id')->constrained('game_rounds')->cascadeOnDelete();
                $table->unsignedInteger('window_number')->default(1);
                $table->string('status', 20)->default('open');
                $table->timestamp('started_at')->nullable();
                $table->timestamp('ended_at')->nullable();
                $table->timestamps();
            });
        }

        Schema::table('bets', function (Blueprint $table) {
            if (!Schema::hasColumn('bets', 'betting_window_id')) {
                $table->foreignId('betting_window_id')->nullable()->after('game_round_id')->constrained('betting_windows')->nullOnDelete();
            }
            if (!Schema::hasColumn('bets', 'cancelled_at')) {
                $table->timestamp('cancelled_at')->nullable()->after('status');
            }
            if (!Schema::hasColumn('bets', 'profit_amount')) {
                $table->decimal('profit_amount', 12, 2)->default(0)->after('payout_amount');
            }
        });

        if (!Schema::hasTable('audit_logs')) {
            Schema::create('audit_logs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('admin_id')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('room_id')->nullable()->constrained('rooms')->nullOnDelete();
                $table->foreignId('game_round_id')->nullable()->constrained('game_rounds')->nullOnDelete();
                $table->string('action', 80);
                $table->text('previous_state')->nullable();
                $table->text('new_state')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');

        Schema::table('bets', function (Blueprint $table) {
            if (Schema::hasColumn('bets', 'betting_window_id')) {
                $table->dropConstrainedForeignId('betting_window_id');
            }
            if (Schema::hasColumn('bets', 'cancelled_at')) {
                $table->dropColumn('cancelled_at');
            }
            if (Schema::hasColumn('bets', 'profit_amount')) {
                $table->dropColumn('profit_amount');
            }
        });

        Schema::dropIfExists('betting_windows');

        Schema::table('game_rounds', function (Blueprint $table) {
            foreach (['payout_mode', 'first_card_matched', 'payout_locked'] as $col) {
                if (Schema::hasColumn('game_rounds', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
