<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('exam_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // The Examinee
            $table->dateTime('started_at');
            $table->dateTime('submitted_at')->nullable();
            $table->integer('remaining_time_seconds')->nullable();
            $table->integer('pause_count')->default(0);
            $table->enum('status', ['in_progress', 'completed', 'graded'])->default('in_progress');
            $table->decimal('total_score', 8, 2)->default(0);
            $table->timestamps();
        });

        // We also need a table for the specific answers within that submission
        Schema::create('exam_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_submission_id')->constrained()->onDelete('cascade');
            $table->foreignId('exam_question_id')->constrained();
            $table->longText('answer_text')->nullable();
            $table->decimal('points_given', 8, 2)->default(0); // Only updated by Teacher
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exam_submissions');
    }
};
