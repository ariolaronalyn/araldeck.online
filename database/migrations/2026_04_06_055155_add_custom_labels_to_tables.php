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
        Schema::table('users', function (Blueprint $table) {
            // Stores your custom labels as a JSON array: ["Definition", "Memorize", etc.]
            $table->json('custom_labels')->nullable();
        });

        Schema::table('flashcards', function (Blueprint $table) {
            // Stores active tags for this card: ["Memorize", "Cases"]
            $table->json('labels')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tables', function (Blueprint $table) {
            //
        });
    }
};
