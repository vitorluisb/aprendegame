<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sudoku_moves', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('session_id')->constrained('sudoku_sessions')->cascadeOnDelete();
            $table->unsignedTinyInteger('row_index');
            $table->unsignedTinyInteger('col_index');
            $table->unsignedTinyInteger('value');
            $table->boolean('is_correct');
            $table->timestamps();

            $table->index(['session_id', 'row_index', 'col_index']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sudoku_moves');
    }
};
