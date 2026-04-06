<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::table('flashcards', function (Blueprint $table) {
            // Deck Type & Difficulty
            $table->enum('deck_type', ['study', 'quiz'])->default('study');
            $table->enum('difficulty', ['easy', 'average', 'hard'])->default('average');
            
            // Quiz Settings
            $table->integer('timer_seconds')->nullable(); // Timer per question
            $table->boolean('show_answer_instantly')->default(true); // During vs End
            
            // Collaboration
            $table->boolean('allow_collaboration')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
