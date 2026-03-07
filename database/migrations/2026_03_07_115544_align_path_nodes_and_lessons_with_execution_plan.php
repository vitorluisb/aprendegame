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
        Schema::table('path_nodes', function (Blueprint $table) {
            $table->enum('node_type', ['lesson', 'review', 'bonus', 'boss'])->default('lesson')->change();
            $table->unsignedSmallInteger('xp_reward')->default(100)->after('node_type');
        });

        Schema::table('lessons', function (Blueprint $table) {
            $table->enum('lesson_type', ['practice', 'review', 'boss'])->default('practice')->after('title');
            $table->unsignedSmallInteger('order')->default(1)->after('lesson_type');
            $table->unsignedSmallInteger('xp_reward')->default(100)->after('difficulty');
            $table->index(['node_id', 'order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lessons', function (Blueprint $table) {
            $table->dropIndex(['node_id', 'order']);
            $table->dropColumn(['lesson_type', 'order', 'xp_reward']);
        });

        Schema::table('path_nodes', function (Blueprint $table) {
            $table->enum('node_type', ['lesson', 'boss'])->default('lesson')->change();
            $table->dropColumn('xp_reward');
        });
    }
};
