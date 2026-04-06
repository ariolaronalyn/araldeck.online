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
        Schema::create('deck_progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('deck_name');
            $table->integer('subject_id');
            $table->string('deck_type');
            $table->integer('current_index')->default(0);
            $table->integer('remaining_seconds')->nullable();
            $table->json('deck_order')->nullable(); // Stores the IDs of cards to remember skips/shuffles
            $table->integer('score')->default(0);
            $table->timestamps();
            
            $table->unique(['user_id', 'deck_name', 'subject_id', 'deck_type'], 'user_deck_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('deck_progress');
    }
};
