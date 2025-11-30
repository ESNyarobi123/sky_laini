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
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('line_request_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('type'); // sms, ussd, push, email, whatsapp
            $table->string('channel')->nullable(); // sms, ussd, push, email, whatsapp
            $table->string('recipient'); // phone, email, etc.
            $table->string('subject')->nullable();
            $table->text('message');
            $table->string('status')->default('pending'); // pending, sent, failed, delivered
            $table->text('error_message')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();
            
            $table->index(['user_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
