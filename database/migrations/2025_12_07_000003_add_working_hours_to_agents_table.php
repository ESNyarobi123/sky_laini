<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('agents', function (Blueprint $table) {
            $table->json('working_hours')->nullable()->after('service_radius_km');
            $table->string('timezone')->default('Africa/Dar_es_Salaam')->after('working_hours');
            $table->boolean('auto_offline')->default(true)->after('timezone');
        });
    }

    public function down(): void
    {
        Schema::table('agents', function (Blueprint $table) {
            $table->dropColumn(['working_hours', 'timezone', 'auto_offline']);
        });
    }
};
