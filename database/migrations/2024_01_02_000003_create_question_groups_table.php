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
        Schema::create('question_groups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_id')->constrained()->cascadeOnDelete();
            $table->foreignId('part_id')->constrained()->cascadeOnDelete();
            $table->text('passage')->nullable();          // Đoạn văn cho Part 3,4,6,7
            $table->string('audio_path')->nullable();     // File audio cho Part 1-4
            $table->string('image_path')->nullable();     // Hình ảnh cho Part 1
            $table->unsignedInteger('order_number');      // Thứ tự trong đề thi
            $table->timestamps();

            $table->index(['exam_id', 'part_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('question_groups');
    }
};
