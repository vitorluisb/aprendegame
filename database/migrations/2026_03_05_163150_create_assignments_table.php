<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('class_id')->constrained()->cascadeOnDelete();
            $table->foreignId('teacher_id')->constrained('users')->restrictOnDelete();
            $table->enum('type', ['nodes', 'lesson', 'simulation']);
            $table->string('title');
            $table->text('description')->nullable();
            $table->timestamp('due_at');
            $table->json('config')->nullable(); // configurações do simulado
            $table->timestamps();

            $table->index(['class_id', 'due_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assignments');
    }
};
