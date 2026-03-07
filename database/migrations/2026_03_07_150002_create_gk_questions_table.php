<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gk_questions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('category_id')->constrained('gk_categories')->cascadeOnDelete();
            $table->text('question_text');
            $table->string('option_a', 500);
            $table->string('option_b', 500);
            $table->string('option_c', 500);
            $table->string('option_d', 500);
            $table->enum('correct_option', ['A', 'B', 'C', 'D']);
            $table->text('explanation')->nullable();
            $table->enum('difficulty', ['easy', 'medium', 'hard']);
            $table->string('age_group', 50)->nullable();
            $table->boolean('is_active')->default(true);
            $table->string('source_reference')->nullable();
            $table->json('metadata_json')->nullable();
            $table->timestamps();

            $table->index(['difficulty', 'is_active']);
            $table->index(['category_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gk_questions');
    }
};
