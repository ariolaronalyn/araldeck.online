<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Prevent same Class Name in the same School Year for a teacher
        Schema::table('school_classes', function (Blueprint $table) {
            // $table->unique(['teacher_id', 'name', 'school_year'], 'unique_class_sy');
        });

        // Prevent duplicate deck schedules for the same class
        Schema::table('class_decks', function (Blueprint $table) {
            // $table->unique(['school_class_id', 'deck_name', 'subject_id'], 'unique_class_schedule');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('classroom_tables', function (Blueprint $table) {
            //
        });
    }
};
