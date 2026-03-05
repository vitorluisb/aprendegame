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
            $table->string('path_type')->default('regular')->after('published');
            // regular | enem | vestibular_fuvest | vestibular_unicamp
            $table->index('path_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('paths', function (Blueprint $table) {
            $table->dropIndex(['path_type']);
            $table->dropColumn('path_type');
        });
    }
};
