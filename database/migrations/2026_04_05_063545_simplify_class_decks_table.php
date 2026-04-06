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
        Schema::table('class_decks', function (Blueprint $table) {
            // Remove columns that are now redundant because we use deck_id
            if (Schema::hasColumn('class_decks', 'deck_name')) {
                $table->dropColumn('deck_name');
            }
            if (Schema::hasColumn('class_decks', 'subject_id')) {
                $table->dropColumn('subject_id');
            }
        });
    }

    public function down()
    {
        Schema::table('class_decks', function (Blueprint $table) {
            $table->string('deck_name')->nullable();
            $table->unsignedBigInteger('subject_id')->nullable();
        });
    }
};
