<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('classes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('grade_id')->constrained()->restrictOnDelete();
            $table->string('name'); // "Turma A", "6º B"
            $table->unsignedSmallInteger('year');
            $table->boolean('active')->default(true);
            $table->timestamps();

            $table->index(['school_id', 'grade_id', 'year']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('classes');
    }
};
