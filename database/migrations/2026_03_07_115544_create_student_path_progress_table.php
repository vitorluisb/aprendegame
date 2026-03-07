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
        Schema::create('student_path_progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('path_id')->constrained()->cascadeOnDelete();
            $table->enum('status', ['locked', 'unlocked', 'in_progress', 'completed'])->default('locked');
            $table->unsignedSmallInteger('current_node_order')->default(1);
            $table->unsignedInteger('xp_earned')->default(0);
            $table->unsignedInteger('xp_total')->default(0);
            $table->unsignedTinyInteger('stars')->default(0);
            $table->decimal('accuracy_percent', 5, 2)->nullable();
            $table->unsignedInteger('attempts_count')->default(0);
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->unique(['student_id', 'path_id']);
            $table->index(['student_id', 'status']);
            $table->index(['path_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_path_progress');
    }
};
