<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * This table stores liveness detection face verification images
     * The agent must take photos facing: left, right, up, down, center
     */
    public function up(): void
    {
        Schema::create('agent_face_verifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agent_id')->constrained()->onDelete('cascade');
            
            // Face images from different angles for liveness detection
            $table->string('face_center')->nullable(); // Looking straight
            $table->string('face_left')->nullable();   // Looking left
            $table->string('face_right')->nullable();  // Looking right
            $table->string('face_up')->nullable();     // Looking up
            $table->string('face_down')->nullable();   // Looking down
            
            // Verification status
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('rejection_reason')->nullable();
            
            // Admin verification
            $table->foreignId('verified_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('verified_at')->nullable();
            
            // Metadata
            $table->string('device_info')->nullable(); // Device used to capture
            $table->string('ip_address')->nullable();
            $table->json('metadata')->nullable(); // Additional metadata (coordinates, etc.)
            
            $table->timestamps();
            
            $table->index(['agent_id', 'status']);
        });

        // Add face_verified column to agents table
        Schema::table('agents', function (Blueprint $table) {
            $table->boolean('face_verified')->default(false)->after('is_verified');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('agent_face_verifications');
        
        Schema::table('agents', function (Blueprint $table) {
            $table->dropColumn('face_verified');
        });
    }
};
