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
        Schema::create('games', function (Blueprint $table) {
            $table->id();
            $table->string('sharable_id', 8)->unique()->nullable();
            $table->string('name');
            $table->string('status')->nullable();
            $table->string('slug')->unique();
            $table->string('password', 1024)->nullable();

            $table->foreignId('manager_id')
                ->constrained('users', 'id')
                ->cascadeOnDelete();

            $table->dateTime('started_at')->nullable();
            $table->dateTime('finished_at')->nullable();
            $table->timestamps();

            $table->unique(['slug', 'manager_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('games');
    }
};
