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
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->string('booking_number', 20)->unique();
            $table->foreignId('customer_id')->constrained('customers')->onDelete('cascade');
            $table->foreignId('agent_id')->nullable()->constrained('agents')->nullOnDelete();
            $table->foreignId('line_request_id')->nullable()->constrained('line_requests')->nullOnDelete();
            
            // Booking details
            $table->enum('line_type', ['vodacom', 'airtel', 'tigo', 'halotel', 'zantel']);
            $table->date('scheduled_date');
            $table->time('scheduled_time');
            $table->enum('time_slot', ['morning', 'afternoon', 'evening'])->nullable();
            
            // Location
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->string('address')->nullable();
            $table->string('phone', 20);
            
            // Status
            $table->enum('status', [
                'pending',      // Waiting for agent to accept
                'confirmed',    // Agent accepted
                'in_progress',  // Day of booking, agent is on the way
                'completed',    // Successfully done
                'cancelled',    // Cancelled by customer or agent
                'expired'       // Not confirmed in time
            ])->default('pending');
            
            // Special requests
            $table->text('notes')->nullable();
            $table->boolean('is_recurring')->default(false);
            $table->enum('recurrence_type', ['daily', 'weekly', 'monthly'])->nullable();
            
            // Agent response
            $table->timestamp('agent_confirmed_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->string('cancellation_reason')->nullable();
            $table->enum('cancelled_by', ['customer', 'agent', 'system'])->nullable();
            
            // Reminder settings
            $table->boolean('reminder_sent')->default(false);
            $table->timestamp('reminder_sent_at')->nullable();
            
            $table->timestamps();
            
            $table->index(['scheduled_date', 'status']);
            $table->index(['customer_id', 'status']);
            $table->index(['agent_id', 'scheduled_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
