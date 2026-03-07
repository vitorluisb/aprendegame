<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('path_nodes', function (Blueprint $table): void {
            $table->unsignedSmallInteger('xp_reward')->default(40)->change();
        });

        Schema::table('lessons', function (Blueprint $table): void {
            $table->unsignedSmallInteger('xp_reward')->default(40)->change();
        });

        Schema::table('daily_missions', function (Blueprint $table): void {
            $table->unsignedSmallInteger('xp_reward')->default(5)->change();
        });

        DB::table('path_nodes')
            ->where('node_type', 'boss')
            ->where('xp_reward', '>', 60)
            ->update(['xp_reward' => 60]);

        DB::table('path_nodes')
            ->where('node_type', '!=', 'boss')
            ->where('xp_reward', '>', 40)
            ->update(['xp_reward' => 40]);

        DB::table('lessons')
            ->where('xp_reward', '>', 40)
            ->update(['xp_reward' => 40]);

        DB::table('daily_missions')
            ->where('xp_reward', '>', 5)
            ->update(['xp_reward' => 5]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('path_nodes', function (Blueprint $table): void {
            $table->unsignedSmallInteger('xp_reward')->default(100)->change();
        });

        Schema::table('lessons', function (Blueprint $table): void {
            $table->unsignedSmallInteger('xp_reward')->default(100)->change();
        });

        Schema::table('daily_missions', function (Blueprint $table): void {
            $table->unsignedSmallInteger('xp_reward')->default(10)->change();
        });
    }
};
