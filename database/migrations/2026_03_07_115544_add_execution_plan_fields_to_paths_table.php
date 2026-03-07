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
        Schema::table('paths', function (Blueprint $table) {
            $table->unsignedTinyInteger('bimester')->nullable()->after('title');
            $table->text('description')->nullable()->after('bimester');
            $table->json('bncc_skills')->nullable()->after('description');
            $table->unsignedInteger('xp_total')->default(0)->after('bncc_skills');
            $table->foreignId('unlocks_after_path_id')->nullable()->after('xp_total')->constrained('paths')->nullOnDelete();
            $table->unsignedSmallInteger('order')->default(1)->after('unlocks_after_path_id');

            $table->index(['grade_id', 'path_type', 'order']);
            $table->index('unlocks_after_path_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('paths', function (Blueprint $table) {
            $table->dropIndex(['grade_id', 'path_type', 'order']);
            $table->dropIndex(['unlocks_after_path_id']);

            $table->dropConstrainedForeignId('unlocks_after_path_id');
            $table->dropColumn([
                'bimester',
                'description',
                'bncc_skills',
                'xp_total',
                'order',
            ]);
        });
    }
};
