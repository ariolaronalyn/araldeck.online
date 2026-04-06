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
            $table->json('details')->nullable()->after('total_questions'); // Stores Q, A, UserA, Time
            $table->json('override_logs')->nullable()->after('details'); // Logs teacher changes
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
