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
        Schema::create('agents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('nida_number')->unique();
            $table->string('phone')->unique();
            $table->string('tier')->default('bronze');
            $table->decimal('rating', 3, 2)->default(0.00);
            $table->integer('total_ratings')->default(0);
            $table->integer('total_completed_requests')->default(0);
            $table->decimal('total_earnings', 12, 2)->default(0.00);
            $table->boolean('is_verified')->default(false);
            $table->boolean('is_available')->default(false);
            $table->boolean('is_online')->default(false);
            $table->decimal('current_latitude', 10, 8)->nullable();
            $table->decimal('current_longitude', 11, 8)->nullable();
            $table->timestamp('last_location_update')->nullable();
            $table->string('specialization')->nullable(); // Comma-separated line types
            $table->integer('service_radius_km')->default(5);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('agents');
    }
};
