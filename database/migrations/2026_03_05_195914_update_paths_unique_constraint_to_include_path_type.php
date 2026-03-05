<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('paths', function (Blueprint $table) {
            // Add new unique constraint first (covers grade_id as prefix, satisfying FK)
            $table->unique(['grade_id', 'subject_id', 'path_type']);
        });

        Schema::table('paths', function (Blueprint $table) {
            // Now safe to drop the old constraint
            $table->dropUnique(['grade_id', 'subject_id']);
        });
    }

    public function down(): void
    {
        Schema::table('paths', function (Blueprint $table) {
            $table->unique(['grade_id', 'subject_id']);
        });

        Schema::table('paths', function (Blueprint $table) {
            $table->dropUnique(['grade_id', 'subject_id', 'path_type']);
        });
    }
};
