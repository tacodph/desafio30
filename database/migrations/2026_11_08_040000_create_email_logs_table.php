<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('email_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('category')->default('platform')->index();
            $table->string('recipient_email')->index();
            $table->json('recipients')->nullable();
            $table->json('sender')->nullable();
            $table->json('reply_to')->nullable();
            $table->text('subject');
            $table->longText('body_text')->nullable();
            $table->longText('body_html')->nullable();
            $table->string('status')->default('queued')->index();
            $table->string('mailer')->nullable();
            $table->unsignedInteger('attempts')->default(0);
            $table->text('error')->nullable();
            $table->text('message_id')->nullable();
            $table->timestamp('attempted_at')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('email_logs');
    }
};
