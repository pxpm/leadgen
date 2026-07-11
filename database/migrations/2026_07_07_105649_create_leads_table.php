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
        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('industry_id')->nullable()->constrained()->nullOnDelete();
            $table->string('status', 50)->default('new');
            $table->string('source', 50)->default('widget');
            $table->string('service_type')->nullable();
            $table->json('pending_services')->nullable();
            $table->unsignedSmallInteger('qualification_score')->nullable();
            $table->text('notes')->nullable();
            $table->string('session_token', 128)->unique();
            $table->timestamp('conversation_started_at')->nullable();
            $table->timestamp('qualified_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leads');
    }
};
