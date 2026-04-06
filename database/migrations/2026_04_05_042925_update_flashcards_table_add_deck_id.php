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
        Schema::table('flashcards', function (Blueprint $table) {
            $table->foreignId('deck_id')->after('user_id')->nullable()->constrained()->onDelete('cascade');
            // We keep deck_name for now just to migrate data, then we can drop it later
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
