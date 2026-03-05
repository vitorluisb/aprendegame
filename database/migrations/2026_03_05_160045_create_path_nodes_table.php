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
        Schema::create('path_nodes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('path_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('order');
            $table->string('title');
            $table->enum('node_type', ['lesson', 'boss'])->default('lesson');
            $table->json('skill_ids'); // array de bncc_skill ids
            $table->boolean('published')->default(false);
            $table->timestamps();

            $table->unique(['path_id', 'order']);
            $table->index(['path_id', 'order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('path_nodes');
    }
};
