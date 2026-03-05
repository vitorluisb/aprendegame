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
        Schema::table('lessons', function (Blueprint $table) {
            $table->unsignedTinyInteger('interaction_count')->default(10)->after('title');
            $table->unsignedTinyInteger('difficulty')->default(2)->after('interaction_count');
            $table->foreign('node_id')->references('id')->on('path_nodes')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('lessons', function (Blueprint $table) {
            $table->dropColumn(['interaction_count', 'difficulty']);
        });
    }
};
