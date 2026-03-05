<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('league_snapshots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('school_id')->nullable();
            $table->unsignedBigInteger('class_id')->nullable();
            $table->string('league'); // bronze | silver | gold | platinum
            $table->unsignedInteger('weekly_xp');
            $table->unsignedSmallInteger('rank_position');
            $table->unsignedSmallInteger('week'); // número da semana ISO
            $table->unsignedSmallInteger('year');
            $table->timestamps();

            $table->index(['year', 'week', 'league']);
            $table->index(['student_id', 'year', 'week']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('league_snapshots');
    }
};
