<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasColumn('students', 'lives_current') || ! Schema::hasColumn('students', 'lives_max')) {
            return;
        }

        DB::statement('UPDATE students SET lives_max = 10, lives_current = 10');
        DB::statement('ALTER TABLE students MODIFY lives_current TINYINT UNSIGNED NOT NULL DEFAULT 10');
        DB::statement('ALTER TABLE students MODIFY lives_max TINYINT UNSIGNED NOT NULL DEFAULT 10');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasColumn('students', 'lives_current') || ! Schema::hasColumn('students', 'lives_max')) {
            return;
        }

        DB::statement('UPDATE students SET lives_max = 5, lives_current = LEAST(lives_current, 5)');
        DB::statement('ALTER TABLE students MODIFY lives_current TINYINT UNSIGNED NOT NULL DEFAULT 5');
        DB::statement('ALTER TABLE students MODIFY lives_max TINYINT UNSIGNED NOT NULL DEFAULT 5');
    }
};
