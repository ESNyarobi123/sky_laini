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
        Schema::create('referrals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('referrer_id')->constrained('users')->onDelete('cascade'); // User who referred
            $table->foreignId('referred_id')->constrained('users')->onDelete('cascade'); // User who was referred
            $table->string('referral_code', 20);
            $table->enum('referrer_type', ['customer', 'agent']); // Type of referrer
            $table->enum('referred_type', ['customer', 'agent']); // Type of referred user
            $table->decimal('bonus_amount', 10, 2)->default(0); // Bonus earned
            $table->decimal('discount_amount', 10, 2)->default(0); // Discount given to referred
            $table->enum('status', ['pending', 'completed', 'rewarded'])->default('pending');
            $table->timestamp('completed_at')->nullable(); // When referred user completed first transaction
            $table->timestamp('rewarded_at')->nullable(); // When bonus was paid
            $table->timestamps();

            $table->unique(['referrer_id', 'referred_id']);
            $table->index('referral_code');
        });

        // Add referral code to users table
        Schema::table('users', function (Blueprint $table) {
            $table->string('referral_code', 20)->nullable()->unique()->after('device_type');
            $table->foreignId('referred_by')->nullable()->constrained('users')->nullOnDelete()->after('referral_code');
            $table->integer('referral_count')->default(0)->after('referred_by');
            $table->decimal('referral_earnings', 10, 2)->default(0)->after('referral_count');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['referred_by']);
            $table->dropColumn(['referral_code', 'referred_by', 'referral_count', 'referral_earnings']);
        });

        Schema::dropIfExists('referrals');
    }
};
