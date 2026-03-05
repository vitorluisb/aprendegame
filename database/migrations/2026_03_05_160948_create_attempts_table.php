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
        Schema::create('attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('question_id')->constrained()->restrictOnDelete();
            $table->foreignId('run_id')->nullable()->constrained('lesson_runs')->nullOnDelete();
            $table->boolean('correct');
            $table->unsignedInteger('time_ms');
            $table->string('given_answer')->nullable();
            $table->timestamps();

            $table->index(['student_id', 'question_id', 'created_at']);
            $table->index(['student_id', 'correct', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attempts');
    }
};
