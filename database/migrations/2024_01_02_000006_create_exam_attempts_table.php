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
        Schema::create('exam_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('exam_id')->constrained()->cascadeOnDelete();
            $table->enum('mode', ['practice', 'full_test'])->default('full_test');
            $table->unsignedSmallInteger('listening_score')->nullable();     // 5-495
            $table->unsignedSmallInteger('reading_score')->nullable();       // 5-495
            $table->unsignedSmallInteger('total_score')->nullable();         // 10-990
            $table->unsignedSmallInteger('total_correct')->default(0);
            $table->unsignedSmallInteger('total_questions')->default(0);
            $table->unsignedInteger('time_spent_seconds')->default(0);
            $table->json('parts_attempted')->nullable();                     // Practice mode: [1,3,5]
            $table->enum('status', ['in_progress', 'completed', 'abandoned'])
                  ->default('in_progress');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index(['exam_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exam_attempts');
    }
};
