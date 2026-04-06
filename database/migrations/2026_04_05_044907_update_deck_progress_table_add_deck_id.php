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
        Schema::table('deck_progress', function (Blueprint $table) {
            $table->foreignId('deck_id')->nullable()->after('user_id')->constrained('decks')->onDelete('cascade');
            
            // Optional: Remove old name-based unique constraint if you had one
            // $table->dropUnique('user_deck_unique'); 
            
            // Add new unique constraint so a user only has one progress per deck
            $table->unique(['user_id', 'deck_id'], 'user_deck_progress_unique');
        });
    }

    public function down()
    {
        Schema::table('deck_progress', function (Blueprint $table) {
            $table->dropUnique('user_deck_progress_unique');
            $table->dropForeign(['deck_id']);
            $table->dropColumn('deck_id');
        });
    }
};
