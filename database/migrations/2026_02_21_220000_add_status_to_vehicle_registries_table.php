<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasColumn('vehicle_registries', 'status')) {
            Schema::table('vehicle_registries', function (Blueprint $table) {
                $table->string('status', 20)->default('available')->after('odometer');
            });
        }

        DB::table('vehicle_registries')
            ->where('is_out_of_service', true)
            ->update(['status' => 'retired']);

        DB::table('vehicle_registries')
            ->whereNull('status')
            ->update(['status' => 'available']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Intentionally left blank: status is now part of the base schema.
    }
};
