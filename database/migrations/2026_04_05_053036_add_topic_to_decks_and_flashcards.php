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
        Schema::table('decks', function (Blueprint $table) {
            $table->string('topic')->nullable()->after('name');
        });
        Schema::table('flashcards', function (Blueprint $table) {
            $table->string('topic')->nullable()->after('answer');
        });
    }

    public function down()
    {
        Schema::table('decks', function (Blueprint $table) { $table->dropColumn('topic'); });
        Schema::table('flashcards', function (Blueprint $table) { $table->dropColumn('topic'); });
    }
};
