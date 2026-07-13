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
        Schema::create('lead_email_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lead_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tenant_email_account_id')->nullable()->constrained()->nullOnDelete();
            $table->string('direction', 10);
            $table->unsignedBigInteger('message_uid')->nullable();
            $table->string('message_id_header', 500)->nullable();
            $table->string('in_reply_to_header', 500)->nullable();
            $table->text('references_header')->nullable();
            $table->string('subject', 500)->nullable();
            $table->text('body_text')->nullable();
            $table->text('body_html')->nullable();
            $table->string('from_address', 255);
            $table->string('from_name', 255)->nullable();
            $table->json('to_addresses')->nullable();
            $table->json('cc_addresses')->nullable();
            $table->json('attachment_media_ids')->nullable();
            $table->json('raw_headers')->nullable();
            $table->json('ai_extracted_fields')->nullable();
            $table->timestamp('received_at')->nullable();
            $table->timestamps();

            $table->unique(['tenant_email_account_id', 'message_uid']);
            $table->index('lead_id');
            $table->index('from_address');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lead_email_messages');
    }
};
