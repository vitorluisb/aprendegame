<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gk_session_answers', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('session_id')->constrained('gk_sessions')->cascadeOnDelete();
            $table->foreignId('question_id')->constrained('gk_questions')->cascadeOnDelete();
            $table->enum('selected_option', ['A', 'B', 'C', 'D']);
            $table->boolean('is_correct');
            $table->unsignedInteger('response_time_ms')->default(0);
            $table->timestamps();

            $table->index(['session_id', 'question_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gk_session_answers');
    }
};
