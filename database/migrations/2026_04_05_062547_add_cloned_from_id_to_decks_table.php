<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('decks', function (Blueprint $table) {
            // Add the column as a nullable foreign key
            $table->unsignedBigInteger('cloned_from_id')->nullable()->after('user_id');
            
            // Optional: Add a foreign key constraint
            $table->foreign('cloned_from_id')->references('id')->on('decks')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('decks', function (Blueprint $table) {
            $table->dropForeign(['cloned_from_id']);
            $table->dropColumn('cloned_from_id');
        });
    }
};