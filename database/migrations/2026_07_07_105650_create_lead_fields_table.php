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
        Schema::create('lead_fields', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lead_id')->constrained()->cascadeOnDelete();
            $table->foreignId('lead_service_id')->nullable();
            $table->string('field_key', 100);
            $table->string('field_type', 50)->default('text');
            $table->text('field_value')->nullable();
            $table->json('field_options')->nullable();
            $table->decimal('confidence', 3, 2)->nullable();
            $table->boolean('is_required')->default(false);
            $table->timestamps();

            $table->unique(['lead_id', 'field_key', 'lead_service_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lead_fields');
    }
};
