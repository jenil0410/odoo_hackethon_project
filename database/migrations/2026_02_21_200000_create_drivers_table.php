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
        Schema::create('drivers', function (Blueprint $table) {
            $table->id();
            $table->string('full_name');
            $table->string('email')->nullable()->unique();
            $table->string('phone_number', 20)->nullable();
            $table->string('license_number')->unique();
            $table->date('license_expiry_date');
            $table->unsignedInteger('total_trips')->default(0);
            $table->unsignedInteger('completed_trips')->default(0);
            $table->decimal('safety_score', 5, 2)->default(0);
            $table->string('status', 20)->default('off_duty'); // on_duty, off_duty, suspended
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('drivers');
    }
};
