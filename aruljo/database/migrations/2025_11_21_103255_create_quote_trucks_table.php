<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quote_trucks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quote_version_id')->constrained('quote_versions')->onDelete('cascade');
            $table->foreignId('truck_type_id')->constrained('tp_truck_types')->onDelete('cascade');
            $table->string('body_type')->default('Truck');
            $table->decimal('truck_cost', 12, 2)->default(0);
            $table->decimal('total_weight', 12, 2)->default(0); // 🆕 snapshot of total truck weight
            $table->decimal('distance_km', 8, 2)->nullable();
            $table->decimal('rate_per_km', 12, 2)->nullable();
            $table->decimal('fixed_rate', 12, 2)->nullable();
            $table->decimal('multiplier', 8, 2)->default(1);
            $table->decimal('unloading_charges', 10, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quote_trucks');
    }
};
