<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('quote_trucks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quote_version_id')->constrained('quote_versions')->onDelete('cascade');
            $table->foreignId('truck_type_id')->constrained('tp_truck_types')->onDelete('cascade');
            $table->string('body_type')->default('Truck');
            $table->integer('truck_count')->default(1);
            $table->decimal('truck_cost', 12, 2)->default(0);
            $table->decimal('distance_km', 8, 2)->nullable();
            $table->decimal('multiplier', 8, 2)->default(1);
            $table->timestamps();
        });

    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quote_trucks');
    }
};
