<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ai_jobs', function (Blueprint $table): void {
            $table->uuid('batch_uuid')->nullable()->after('id');
            $table->unsignedSmallInteger('requested_count')->default(0)->after('model');
            $table->index(['batch_uuid', 'status']);
            $table->index(['status', 'created_at']);
        });

        Schema::table('questions', function (Blueprint $table): void {
            $table->index(['skill_id', 'ai_generated', 'status', 'created_at'], 'questions_skill_ai_status_created_index');
        });
    }

    public function down(): void
    {
        Schema::table('questions', function (Blueprint $table): void {
            $table->dropIndex('questions_skill_ai_status_created_index');
        });

        Schema::table('ai_jobs', function (Blueprint $table): void {
            $table->dropIndex(['batch_uuid', 'status']);
            $table->dropIndex(['status', 'created_at']);
            $table->dropColumn(['batch_uuid', 'requested_count']);
        });
    }
};
