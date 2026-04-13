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
        Schema::table('exam_submissions', function (Blueprint $table) {
            // To store tab-switching incidents
            $table->json('proctoring_logs')->nullable(); 
            // To store time per question: {"q1": 120, "q2": 45}
            $table->json('time_per_question')->nullable(); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('exam_submissions', function (Blueprint $table) {
            //
        });
    }
};
