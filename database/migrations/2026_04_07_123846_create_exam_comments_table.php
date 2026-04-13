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
        Schema::create('exam_comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Commenter
            $table->foreignId('exam_submission_id')->constrained()->onDelete('cascade');
            // If you want comments on specific answers:
            $table->foreignId('exam_answer_id')->nullable()->constrained()->onDelete('cascade');
            
            $table->text('comment_body');
            
            // Nested Logic
            $table->unsignedBigInteger('parent_id')->nullable(); 
            $table->foreign('parent_id')->references('id')->on('exam_comments')->onDelete('cascade');
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exam_comments');
    }
};
