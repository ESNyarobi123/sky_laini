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
        Schema::create('line_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('customers')->onDelete('cascade');
            $table->foreignId('agent_id')->nullable()->constrained('agents')->onDelete('set null');
            $table->string('request_number')->unique();
            $table->string('line_type'); // airtel, vodacom, halotel, tigo, zantel
            $table->string('status')->default('pending'); // pending, accepted, in_progress, completed, cancelled
            $table->decimal('customer_latitude', 10, 8);
            $table->decimal('customer_longitude', 11, 8);
            $table->string('customer_address')->nullable();
            $table->string('customer_phone');
            $table->string('confirmation_code')->nullable();
            $table->text('ussd_instructions')->nullable();
            $table->string('payment_link')->nullable();
            $table->decimal('service_fee', 10, 2)->default(0.00);
            $table->decimal('commission', 10, 2)->default(0.00);
            $table->timestamp('accepted_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->text('cancellation_reason')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('line_requests');
    }
};
