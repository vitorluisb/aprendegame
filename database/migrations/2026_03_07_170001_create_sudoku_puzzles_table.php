<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sudoku_puzzles', function (Blueprint $table): void {
            $table->id();
            $table->enum('difficulty', ['easy', 'medium', 'hard']);
            $table->string('puzzle_string', 81);
            $table->string('solution_string', 81);
            $table->unsignedTinyInteger('clues_count');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['difficulty', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sudoku_puzzles');
    }
};
