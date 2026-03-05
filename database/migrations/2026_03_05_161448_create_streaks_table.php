<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('streaks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('current')->default(0);
            $table->unsignedSmallInteger('best')->default(0);
            $table->date('last_activity_date')->nullable();
            $table->timestamp('freeze_used_at')->nullable(); // 1 freeze/semana
            $table->timestamps();

            $table->unique('student_id'); // um streak por aluno
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('streaks');
    }
};
