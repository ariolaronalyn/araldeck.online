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
            $table->string('font_family')->nullable()->default("'Georgia', serif");
            $table->integer('font_size')->nullable()->default(18);
            $table->string('alignment')->nullable()->default('justify');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('deck_progress', function (Blueprint $table) {
            //
        });
    }
};
