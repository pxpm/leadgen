<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->index(['tenant_id', 'status']);
        });

        Schema::table('conversation_messages', function (Blueprint $table) {
            $table->index(['lead_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->dropIndex(['tenant_id', 'status']);
        });

        Schema::table('conversation_messages', function (Blueprint $table) {
            $table->dropIndex(['lead_id', 'created_at']);
        });
    }
};
