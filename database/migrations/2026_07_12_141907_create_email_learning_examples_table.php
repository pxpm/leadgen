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
        Schema::create('email_learning_examples', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('scenario');                              // FollowUpScenario value
            $table->string('reasons_hash', 64);                      // MD5 of sorted reasons for fast lookup
            $table->text('generated_body');                          // AI-generated (before edits)
            $table->text('sent_body');                               // What was actually sent (after edits)
            $table->boolean('was_edited')->default(false);           // Did the contractor edit it?
            $table->timestamps();

            $table->index(['tenant_id', 'scenario', 'reasons_hash']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('email_learning_examples');
    }
};
