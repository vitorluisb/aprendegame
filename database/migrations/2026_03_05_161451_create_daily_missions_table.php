<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('daily_missions', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('description')->nullable();
            $table->string('type'); // study | streak | xp | perfect_lesson
            $table->unsignedSmallInteger('target_value'); // ex: complete 3 lessons
            $table->unsignedSmallInteger('xp_reward')->default(10);
            $table->boolean('active')->default(true);
            $table->timestamps();

            $table->index(['type', 'active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('daily_missions');
    }
};
