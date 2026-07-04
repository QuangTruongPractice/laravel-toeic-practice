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
        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('question_group_id')->constrained()->cascadeOnDelete();
            $table->text('content')->nullable();                // Nội dung câu hỏi (Part 1,2 có thể null)
            $table->unsignedSmallInteger('question_number');    // Số thứ tự câu hỏi (1-200)
            $table->unsignedTinyInteger('order_in_group');      // Thứ tự trong nhóm
            $table->text('explanation')->nullable();            // Giải thích đáp án
            $table->timestamps();

            $table->index('question_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('questions');
    }
};
