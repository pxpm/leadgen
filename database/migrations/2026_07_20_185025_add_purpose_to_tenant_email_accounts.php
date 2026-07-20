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
            $table->string('purpose', 20)->default('both')->after('provider');
        });
    }

    public function down(): void
    {
        Schema::table('tenant_email_accounts', function (Blueprint $table) {
            $table->dropColumn('purpose');
        });
    }
};
