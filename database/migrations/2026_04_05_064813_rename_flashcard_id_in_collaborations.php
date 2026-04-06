<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        Schema::table('collaborations', function (Blueprint $table) {
            // 1. Try to drop the foreign key only if it exists
            // We use a try-catch or raw SQL to avoid the "1091 Can't DROP" error
            try {
                if (DB::getSchemaBuilder()->hasColumn('collaborations', 'flashcard_id')) {
                    $table->dropForeign(['flashcard_id']);
                }
            } catch (\Exception $e) {
                // If it doesn't exist, just ignore and move on
            }

            // 2. Drop the column (this will work as long as the column exists)
            if (DB::getSchemaBuilder()->hasColumn('collaborations', 'flashcard_id')) {
                $table->dropColumn('flashcard_id');
            }

            // 3. Add the new deck_id column
            $table->unsignedBigInteger('deck_id')->nullable()->after('id');
            
            // 4. Create the new relationship
            $table->foreign('deck_id')->references('id')->on('decks')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::table('collaborations', function (Blueprint $table) {
            if (DB::getSchemaBuilder()->hasColumn('collaborations', 'deck_id')) {
                $table->dropForeign(['deck_id']);
                $table->dropColumn('deck_id');
            }
            $table->unsignedBigInteger('flashcard_id')->nullable();
        });
    }
};