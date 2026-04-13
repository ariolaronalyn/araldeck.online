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
        Schema::create('exam_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Creator
            $table->foreignId('course_id')->nullable()->constrained();
            $table->foreignId('subject_id')->nullable()->constrained();
            $table->text('question_text');
            $table->text('correct_answer_guide')->nullable(); // For teachers to refer to
            $table->integer('default_points')->default(5);
            $table->boolean('is_public')->default(false); // Admin/Encoder set this
            $table->unsignedBigInteger('cloned_from_id')->nullable(); // If student/teacher clones a public Q
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exam_questions');
    }
};
