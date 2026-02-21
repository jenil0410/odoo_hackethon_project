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
        Schema::create('trips', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_registry_id')->constrained('vehicle_registries')->cascadeOnDelete();
            $table->foreignId('driver_id')->constrained('drivers')->cascadeOnDelete();
            $table->string('origin_address');
            $table->string('destination_address');
            $table->decimal('cargo_weight', 12, 2); // in kg
            $table->decimal('estimated_fuel_cost', 12, 2)->default(0);
            $table->string('status', 20)->default('draft'); // draft, dispatched, completed, cancelled
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trips');
    }
};
