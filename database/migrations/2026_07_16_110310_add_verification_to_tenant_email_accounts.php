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
        Schema::table('tenant_email_accounts', function (Blueprint $table) {
            $table->string('verification_code', 255)->nullable()->after('auto_create_leads');
            $table->timestamp('verification_code_expires_at')->nullable()->after('verification_code');
            $table->timestamp('verified_at')->nullable()->after('verification_code_expires_at');

            // Change default status to 'pending_verification' for new accounts
            $table->string('status', 20)->default('pending_verification')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tenant_email_accounts', function (Blueprint $table) {
            $table->dropColumn(['verification_code', 'verification_code_expires_at', 'verified_at']);
            $table->string('status', 20)->default('active')->change();
        });
    }
};
