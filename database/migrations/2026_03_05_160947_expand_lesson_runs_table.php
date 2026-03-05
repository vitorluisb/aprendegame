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
        Schema::table('lesson_runs', function (Blueprint $table) {
            $table->timestamp('started_at')->useCurrent()->after('lesson_id');
            $table->timestamp('finished_at')->nullable()->after('started_at');
            $table->unsignedSmallInteger('xp_earned')->default(0)->after('score');
            $table->unsignedTinyInteger('correct_count')->default(0)->after('xp_earned');
            $table->unsignedTinyInteger('total_count')->default(0)->after('correct_count');
            $table->foreign('student_id')->references('id')->on('students')->cascadeOnDelete();
            $table->foreign('lesson_id')->references('id')->on('lessons')->restrictOnDelete();
            $table->index(['student_id', 'started_at']);
        });
    }

    public function down(): void
    {
        Schema::table('lesson_runs', function (Blueprint $table) {
            $table->dropForeign(['student_id', 'lesson_id']);
            $table->dropColumn(['started_at', 'finished_at', 'xp_earned', 'correct_count', 'total_count']);
        });
    }
};
