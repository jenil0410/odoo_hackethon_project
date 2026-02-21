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
        Schema::create('maintenance_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_registry_id')->constrained('vehicle_registries')->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->date('service_date');
            $table->decimal('cost', 12, 2)->default(0);
            $table->string('status', 20)->default('in_shop'); // in_shop, completed
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('maintenance_logs');
    }
};
