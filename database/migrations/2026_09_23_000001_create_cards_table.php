<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cards', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('rank', ['Ace', 'King', 'Queen', 'Jack', 'Number', 'Joker', 'Custom']);
            $table->enum('suit', ['Spades', 'Hearts', 'Diamonds', 'Clubs', 'None'])->nullable();
            $table->integer('value')->nullable();
            $table->string('code')->nullable()->unique();
            $table->text('description')->nullable();
            $table->string('photo_path')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cards');
    }
};
