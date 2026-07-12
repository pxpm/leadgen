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
        Schema::create('follow_up_actions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('lead_id')->constrained()->cascadeOnDelete();
            $table->string('scenario');                         // FollowUpScenario value
            $table->json('selected_items')->nullable();          // reasons, fields, or stage selected
            $table->text('free_text')->nullable();               // contractor's custom notes
            $table->text('generated_email')->nullable();         // AI-generated body
            $table->text('final_email')->nullable();              // what was sent (after edits)
            $table->string('status')->default('draft');          // draft, sent, discarded
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('follow_up_actions');
    }
};
