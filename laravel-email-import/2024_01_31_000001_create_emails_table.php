<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('emails', function (Blueprint $table) {
            $table->id();
            $table->string('message_id')->unique()->nullable();
            $table->string('subject')->nullable();
            $table->string('from_email');
            $table->string('from_name')->nullable();
            $table->json('to')->nullable(); // Array von Empfängern
            $table->json('cc')->nullable();
            $table->json('bcc')->nullable();
            $table->text('text_body')->nullable();
            $table->longText('html_body')->nullable();
            $table->timestamp('email_date')->nullable();
            $table->string('source')->default('unknown'); // 'imap', 'upload'
            $table->boolean('has_attachments')->default(false);
            $table->json('headers')->nullable(); // Alle Email-Header
            $table->timestamps();
            
            $table->index('from_email');
            $table->index('email_date');
            $table->index('source');
        });

        Schema::create('email_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('email_id')->constrained()->onDelete('cascade');
            $table->string('filename');
            $table->string('mime_type')->nullable();
            $table->integer('size')->nullable(); // in bytes
            $table->string('path'); // Pfad zur gespeicherten Datei
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('email_attachments');
        Schema::dropIfExists('emails');
    }
};
