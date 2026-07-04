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
        Schema::create('exams', function (Blueprint $table) {
            $table->id();
            $table->string('title');                                          // e.g. "ETS 2024 Test 1"
            $table->string('slug')->unique();                                 // URL-friendly slug
            $table->text('description')->nullable();
            $table->unsignedSmallInteger('year')->nullable();                 // Năm xuất bản
            $table->enum('status', ['draft', 'published', 'archived'])
                  ->default('draft');
            $table->unsignedInteger('duration_minutes')->default(120);        // 120 phút cho full test
            $table->timestamps();
            $table->softDeletes();

            $table->index('status');
            $table->index('year');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exams');
    }
};
