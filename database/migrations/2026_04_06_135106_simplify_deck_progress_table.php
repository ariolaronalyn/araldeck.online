<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('deck_progress', function (Blueprint $table) {
            // Change old required columns to nullable so they don't block the insert
            $table->string('deck_name')->nullable()->change();
            $table->integer('subject_id')->nullable()->change();
            $table->string('deck_type')->nullable()->change();
        });
    }

    public function down()
    {
        Schema::table('deck_progress', function (Blueprint $table) {
            $table->string('deck_name')->nullable(false)->change();
            $table->integer('subject_id')->nullable(false)->change();
            $table->string('deck_type')->nullable(false)->change();
        });
    }
};
