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
        Schema::table('quiz_attempts', function (Blueprint $table) {
            // Add deck_id and link it to the decks table
            $table->foreignId('deck_id')->nullable()->after('user_id')->constrained('decks')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::table('quiz_attempts', function (Blueprint $table) {
            $table->dropForeign(['deck_id']);
            $table->dropColumn('deck_id');
        });
    }
};
