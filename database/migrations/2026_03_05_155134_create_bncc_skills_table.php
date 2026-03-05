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
        Schema::create('bncc_skills', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->text('description');
            $table->foreignId('grade_id')->constrained()->restrictOnDelete();
            $table->foreignId('subject_id')->constrained()->restrictOnDelete();
            $table->string('thematic_unit')->nullable();
            $table->string('knowledge_object')->nullable();
            $table->unsignedTinyInteger('version')->default(1);
            $table->boolean('active')->default(true);
            $table->timestamps();

            $table->index(['grade_id', 'subject_id']);
            $table->index(['code', 'active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bncc_skills');
    }
};
