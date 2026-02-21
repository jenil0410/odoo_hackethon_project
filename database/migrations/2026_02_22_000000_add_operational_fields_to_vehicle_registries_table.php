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
        Schema::table('vehicle_registries', function (Blueprint $table) {
            $table->boolean('is_in_shop')->default(false)->after('is_out_of_service');
            $table->decimal('acquisition_cost', 12, 2)->default(0)->after('is_in_shop');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vehicle_registries', function (Blueprint $table) {
            $table->dropColumn(['is_in_shop', 'acquisition_cost']);
        });
    }
};
