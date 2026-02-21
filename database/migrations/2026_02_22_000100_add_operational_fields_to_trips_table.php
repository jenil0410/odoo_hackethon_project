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
        Schema::table('trips', function (Blueprint $table) {
            $table->decimal('actual_distance_km', 12, 2)->nullable()->after('estimated_fuel_cost');
            $table->decimal('revenue_amount', 12, 2)->default(0)->after('actual_distance_km');
            $table->decimal('final_odometer', 12, 2)->nullable()->after('revenue_amount');
            $table->timestamp('completed_at')->nullable()->after('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('trips', function (Blueprint $table) {
            $table->dropColumn(['actual_distance_km', 'revenue_amount', 'final_odometer', 'completed_at']);
        });
    }
};
