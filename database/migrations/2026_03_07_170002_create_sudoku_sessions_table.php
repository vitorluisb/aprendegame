<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sudoku_sessions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('puzzle_id')->constrained('sudoku_puzzles')->cascadeOnDelete();
            $table->enum('status', ['active', 'completed', 'abandoned'])->default('active');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->unsignedInteger('elapsed_seconds')->default(0);
            $table->unsignedSmallInteger('mistakes_count')->default(0);
            $table->unsignedTinyInteger('hints_used')->default(0);
            $table->unsignedInteger('reward_xp')->default(0);
            $table->unsignedInteger('reward_gems')->default(0);
            $table->timestamps();

            $table->index(['user_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sudoku_sessions');
    }
};
