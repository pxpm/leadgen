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
            $table->string('connection_type', 20)->default('imap_password')->after('provider');
            $table->text('access_token')->nullable()->after('app_password');
            $table->text('refresh_token')->nullable()->after('access_token');
            $table->json('token_metadata')->nullable()->after('refresh_token');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tenant_email_accounts', function (Blueprint $table) {
            //
        });
    }
};
