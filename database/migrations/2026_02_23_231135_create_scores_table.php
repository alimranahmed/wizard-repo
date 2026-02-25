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
        Schema::create('scores', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('round')->nullable();
            $table->foreignId('game_id')->constrained('games', 'id')->cascadeOnDelete();
            $table->foreignId('member_id')->constrained('members', 'id')->cascadeOnDelete();
            $table->unsignedInteger('target_win');
            $table->unsignedInteger('actual_win')->nullable();
            $table->integer('point');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('scores');
    }
};
