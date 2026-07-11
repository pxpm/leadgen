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
        Schema::create('missed_calls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->nullable()->constrained()->nullOnDelete();
            $table->string('caller_number', 50);
            $table->string('tenant_phone', 50);
            $table->string('twilio_call_sid')->unique();
            $table->string('matched_by', 20)->nullable();
            $table->boolean('sms_sent')->default(false);
            $table->foreignId('lead_id')->nullable()->constrained()->nullOnDelete();
            $table->string('intent', 50)->nullable();
            $table->timestamp('tenant_notified_at')->nullable();
            $table->timestamp('caller_sms_sent_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('missed_calls');
    }
};
