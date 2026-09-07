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
        Schema::create('game_rounds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('room_id')->constrained('rooms')->cascadeOnDelete();
            $table->unsignedBigInteger('round_number');
            $table->string('first_card')->nullable(); // e.g. 2_spades, king_hearts, ace_diamonds
            $table->enum('status', [
                'scheduled',
                'open',
                'betting_open',
                'betting_closed',
                'result_pending',
                'result_declared',
                'round_closed'
            ])->default('open');
            $table->enum('winning_side', ['andar', 'bahar', 'none'])->default('none');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('betting_ends_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('game_rounds');
    }
};
