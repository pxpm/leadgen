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
        Schema::create('calendar_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('category'); // CalendarEventCategory enum value
            $table->dateTime('start_at');
            $table->dateTime('end_at');
            $table->boolean('all_day')->default(false);
            $table->string('location')->nullable();
            $table->string('status')->default('scheduled'); // CalendarEventStatus enum value
            $table->string('color', 7)->nullable(); // hex color
            $table->boolean('is_recurring')->default(false);
            $table->string('recurrence_rule')->nullable(); // RRULE string
            $table->date('recurrence_ends_at')->nullable();
            $table->foreignId('parent_event_id')->nullable()->constrained('calendar_events')->nullOnDelete();
            $table->nullableMorphs('eventable'); // lead, user, etc.
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'start_at']);
            $table->index(['tenant_id', 'category']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('calendar_events');
    }
};
