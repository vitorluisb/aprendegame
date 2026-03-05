<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_jobs', function (Blueprint $table) {
            $table->id();
            $table->string('type'); // generate_questions | explain_error | validate_batch
            $table->foreignId('skill_id')->nullable()->constrained('bncc_skills')->nullOnDelete();
            $table->enum('status', ['pending', 'processing', 'done', 'failed'])->default('pending');
            $table->json('config')->nullable(); // parâmetros do job
            $table->unsignedInteger('prompt_tokens')->default(0);
            $table->unsignedInteger('result_tokens')->default(0);
            $table->string('model')->nullable();
            $table->text('error')->nullable();
            $table->unsignedTinyInteger('questions_generated')->default(0);
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'type']);
            $table->index(['skill_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_jobs');
    }
};
