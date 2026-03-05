<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('badges', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('description')->nullable();
            $table->string('icon')->nullable(); // emoji ou nome de ícone
            $table->string('condition_type'); // streak | xp | lesson | perfect_score
            $table->unsignedInteger('condition_value'); // ex: streak >= 7
            $table->boolean('active')->default(true);
            $table->timestamps();

            $table->index(['condition_type', 'active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('badges');
    }
};
