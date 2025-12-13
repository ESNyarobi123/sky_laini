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
        Schema::create('referral_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('value');
            $table->string('description')->nullable();
            $table->timestamps();
        });

        // Insert default referral settings
        DB::table('referral_settings')->insert([
            [
                'key' => 'customer_referral_bonus',
                'value' => '500',
                'description' => 'Bonus (TSh) for customer when they refer another customer',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'customer_referred_discount',
                'value' => '300',
                'description' => 'Discount (TSh) for referred customer on first request',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'agent_referral_bonus',
                'value' => '1000',
                'description' => 'Bonus (TSh) for agent when they refer another agent',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'agent_referred_bonus',
                'value' => '500',
                'description' => 'Bonus (TSh) for referred agent after first completed job',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'referral_code_length',
                'value' => '8',
                'description' => 'Length of generated referral codes',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'min_jobs_for_reward',
                'value' => '1',
                'description' => 'Minimum completed jobs before referral bonus is paid',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('referral_settings');
    }
};
