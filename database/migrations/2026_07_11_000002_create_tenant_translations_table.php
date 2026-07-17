<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tenant_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained();
            $table->string('locale', 10);
            $table->string('group');
            $table->string('key');
            $table->json('value');
            $table->timestamps();

            $table->unique(['tenant_id', 'locale', 'group', 'key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenant_translations');
    }
};
