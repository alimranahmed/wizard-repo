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
        Schema::create('game_member', function (Blueprint $table) {
            $table->id();
            $table->foreignId('game_id')->constrained('games', 'id')->cascadeOnDelete();
            $table->foreignId('member_id')->constrained('members', 'id')->cascadeOnDelete();
            $table->integer('order')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('game_member');
    }
};
