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
        Schema::create('in_app_notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('line_request_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('type'); // new_request, order_created, payment_received, job_completed, agent_accepted, etc.
            $table->string('title');
            $table->text('message');
            $table->string('icon')->nullable(); // Icon name for display
            $table->string('color')->nullable(); // Color code for notification
            $table->json('data')->nullable(); // Additional data like request_id, agent_id, etc.
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
            
            $table->index(['user_id', 'read_at']);
            $table->index(['user_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('in_app_notifications');
    }
};
