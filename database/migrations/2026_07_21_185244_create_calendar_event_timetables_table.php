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
        Schema::create('calendar_event_timetables', function (Blueprint $table) {
            $table->id();
            $table->foreignId('calendar_event_id')->constrained()->cascadeOnDelete();
            $table->string('day_of_week', 2); // MO, TU, WE, TH, FR, SA, SU
            $table->time('start_time');
            $table->time('end_time');
            $table->timestamps();

            $table->unique(['calendar_event_id', 'day_of_week']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('calendar_event_timetables');
    }
};
