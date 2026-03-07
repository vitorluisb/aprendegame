<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gk_sessions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedInteger('score')->default(0);
            $table->unsignedTinyInteger('current_round')->default(1);
            $table->unsignedTinyInteger('correct_answers')->default(0);
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->unsignedInteger('reward_xp')->default(0);
            $table->unsignedInteger('reward_gems')->default(0);
            $table->enum('status', ['active', 'lost', 'completed'])->default('active');
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index(['started_at', 'finished_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gk_sessions');
    }
};
