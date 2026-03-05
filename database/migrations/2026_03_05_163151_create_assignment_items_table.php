<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assignment_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assignment_id')->constrained()->cascadeOnDelete();
            $table->string('item_type'); // path_node | lesson
            $table->unsignedBigInteger('item_id');
            $table->unsignedSmallInteger('order')->default(0);
            $table->timestamps();

            $table->index(['assignment_id', 'order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assignment_items');
    }
};
