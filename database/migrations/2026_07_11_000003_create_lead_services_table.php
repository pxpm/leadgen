<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lead_services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lead_id')->constrained()->cascadeOnDelete();
            $table->string('service_key');
            $table->string('status')->default('in_progress'); // in_progress, qualified
            $table->unsignedInteger('order')->default(0);
            $table->timestamps();
        });

        Schema::table('lead_fields', function (Blueprint $table) {
            $table->foreignId('lead_service_id')->nullable()->after('lead_id')->constrained('lead_services')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('lead_fields', function (Blueprint $table) {
            $table->dropConstrainedForeignId('lead_service_id');
        });

        Schema::dropIfExists('lead_services');
    }
};
