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
        // Stub mínimo criado na Etapa 1.2 para PathProgressService.
        // Colunas completas adicionadas na Etapa 1.4.
        Schema::create('lesson_runs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('lesson_id')->nullable();
            $table->unsignedTinyInteger('score')->default(0);
            $table->timestamps();

            $table->index(['student_id', 'lesson_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lesson_runs');
    }
};
