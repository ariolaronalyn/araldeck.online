<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up() {
        Schema::create('exams', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained(); // Creator
            $table->string('name');
            $table->enum('type', ['class', 'group', 'self']);
            $table->foreignId('assessment_type_id')->nullable();
            $table->foreignId('course_id')->nullable();
            $table->foreignId('subject_id')->nullable();
            $table->integer('cloned_from_id')->nullable();
            $table->json('collaborators')->nullable();
            
            // Settings
            $table->enum('timer_type', ['per_question', 'overall', 'equal']);
            $table->integer('total_time_minutes')->nullable();
            $table->boolean('allow_pause')->default(false);
            $table->integer('pause_limit')->default(0);
            $table->boolean('is_public')->default(false);
            $table->timestamps();
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
