<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('line_requests', function (Blueprint $table) {
            $table->string('payment_order_id')->nullable()->after('status');
            $table->string('payment_status')->default('pending')->after('payment_order_id'); // pending, paid, failed
        });
    }

    public function down(): void
    {
        Schema::table('line_requests', function (Blueprint $table) {
            $table->dropColumn(['payment_order_id', 'payment_status']);
        });
    }
};
