<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mastery', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('skill_id')->constrained('bncc_skills')->restrictOnDelete();
            $table->unsignedTinyInteger('mastery_score')->default(0); // 0–100
            $table->timestamp('last_seen_at')->nullable();
            $table->timestamp('next_review_at')->nullable();
            $table->unsignedTinyInteger('interval_days')->default(1);
            $table->unsignedTinyInteger('consecutive_correct')->default(0);
            $table->timestamps();

            $table->unique(['student_id', 'skill_id']);
            $table->index(['student_id', 'next_review_at']); // query mais frequente
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mastery');
    }
};
