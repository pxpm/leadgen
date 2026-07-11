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
        Schema::create('tenants', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('locale', 5)->default('pt');
            $table->foreignId('industry_id')->nullable()->constrained()->nullOnDelete();
            $table->string('stripe_customer_id')->nullable();
            $table->string('twilio_phone_number', 50)->nullable();
            $table->string('twilio_phone_sid')->nullable();
            $table->json('branding_config')->nullable();
            $table->json('notification_config')->nullable();
            $table->json('active_services')->nullable();
            $table->json('service_config')->nullable();
            $table->json('qualification_overrides')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tenants');
    }
};
