<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('enem_questions', function (Blueprint $table): void {
            $table->id();
            $table->enum('area', ['linguagens', 'humanas', 'natureza', 'matematica']);
            $table->string('subject', 120);
            $table->string('enem_code', 32)->unique();
            $table->text('context_text');
            $table->text('question_text');
            $table->text('option_a');
            $table->text('option_b');
            $table->text('option_c');
            $table->text('option_d');
            $table->text('option_e');
            $table->enum('correct_option', ['A', 'B', 'C', 'D', 'E']);
            $table->enum('difficulty', ['easy', 'medium', 'hard']);
            $table->unsignedSmallInteger('year_reference')->nullable();
            $table->text('explanation');
            $table->enum('status', ['draft', 'reviewed', 'approved'])->default('draft');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['area', 'subject']);
            $table->index(['difficulty', 'status']);
            $table->index(['year_reference', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('enem_questions');
    }
};
