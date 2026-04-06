<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('collaborations', function (Blueprint $table) {
            // 1. Disable Foreign Keys
            Schema::disableForeignKeyConstraints();

            // 2. Safely drop the old column (and its foreign key if it exists)
            // We use a try-catch or check existence to prevent migration crashes
            if (Schema::hasColumn('collaborations', 'flashcard_id')) {
                // Check if the foreign key exists before dropping
                // If you get an error here, you can skip the dropForeign line
                try {
                    $table->dropForeign(['flashcard_id']);
                } catch (\Exception $e) { }
                
                $table->dropColumn('flashcard_id');
            }

            // 3. Add the new deck_id column if it doesn't exist
            if (!Schema::hasColumn('collaborations', 'deck_id')) {
                $table->foreignId('deck_id')->after('id')->nullable()->constrained('decks')->onDelete('cascade');
            }

            Schema::enableForeignKeyConstraints();
        });
    }

    public function down()
    {
        Schema::table('collaborations', function (Blueprint $table) {
            $table->dropForeign(['deck_id']);
            $table->renameColumn('deck_id', 'flashcard_id');
            $table->foreign('flashcard_id')->references('id')->on('flashcards')->onDelete('cascade');
        });
    }
};