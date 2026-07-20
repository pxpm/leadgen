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
        Schema::create('tenant_email_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained();
            $table->string('provider', 20);
            $table->string('connection_type', 20)->default('imap_password');
            $table->string('email', 255);
            $table->string('name', 255)->nullable();
            $table->text('app_password')->nullable();
            $table->text('access_token')->nullable();
            $table->text('refresh_token')->nullable();
            $table->json('token_metadata')->nullable();
            $table->json('imap_config')->nullable();
            $table->json('smtp_config')->nullable();
            $table->string('status', 20)->default('pending_verification');
            $table->string('watch_folder', 100)->nullable();
            $table->boolean('auto_create_leads')->default(false);
            $table->string('verification_code', 255)->nullable();
            $table->timestamp('verification_code_expires_at')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->timestamp('last_synced_at')->nullable();
            $table->unsignedBigInteger('last_synced_uid')->nullable();
            $table->string('last_error', 500)->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['tenant_id', 'email']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tenant_email_accounts');
    }
};
