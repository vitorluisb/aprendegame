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
        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('skill_id')->constrained('bncc_skills')->restrictOnDelete();
            $table->enum('type', ['multiple_choice', 'true_false', 'fill_blank', 'order_steps', 'drag_drop', 'short_answer']);
            $table->unsignedTinyInteger('difficulty')->default(2); // 1–5
            $table->text('prompt');
            $table->json('options')->nullable();
            $table->text('correct_answer');
            $table->text('explanation')->nullable();
            $table->enum('status', ['draft', 'reviewed', 'published'])->default('draft');
            $table->boolean('ai_generated')->default(false);
            $table->unsignedInteger('avg_time_ms')->default(0);
            $table->decimal('error_rate', 5, 4)->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['skill_id', 'status', 'difficulty']);
            $table->index(['type', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('questions');
    }
};
