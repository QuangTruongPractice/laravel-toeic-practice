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
        Schema::create('parts', function (Blueprint $table) {
            $table->id();
            $table->unsignedTinyInteger('part_number')->unique(); // 1-7
            $table->string('name');                               // e.g. "Photographs"
            $table->enum('section', ['listening', 'reading']);
            $table->text('description')->nullable();
            $table->text('directions')->nullable();               // Hướng dẫn cho mỗi Part
            $table->unsignedInteger('question_count');            // Số câu chuẩn (6, 25, 39...)
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('parts');
    }
};
