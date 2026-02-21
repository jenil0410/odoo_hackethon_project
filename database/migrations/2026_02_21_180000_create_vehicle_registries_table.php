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
        Schema::create('vehicle_registries', function (Blueprint $table) {
            $table->id();
            $table->string('name_model');
            $table->string('license_plate')->unique();
            $table->decimal('max_load_capacity', 12, 2);
            $table->string('load_unit', 10); // kg or tons
            $table->decimal('odometer', 12, 2);
            $table->string('status', 20)->default('available'); // available, on_trip, in_shop, retired
            $table->boolean('is_out_of_service')->default(false);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehicle_registries');
    }
};
