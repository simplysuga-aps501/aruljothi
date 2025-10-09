<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tp_truck_agency_rates', function (Blueprint $table) {
            $table->id();

            // link to agency
            $table->unsignedBigInteger('truck_agency_id');
            $table->foreign('truck_agency_id')
                  ->references('id')
                  ->on('tp_truck_agencies')
                  ->onDelete('cascade');

            // link to truck type
            $table->unsignedBigInteger('truck_type_id');
            $table->foreign('truck_type_id')
                  ->references('id')
                  ->on('tp_truck_types')
                  ->onDelete('cascade');

            // link to location (distance_pincodes)
            $table->unsignedBigInteger('location_id');
            $table->foreign('location_id')
                  ->references('id')
                  ->on('distance_pincodes')
                  ->onDelete('cascade');

            $table->decimal('fixed_rate', 10, 2);

            $table->timestamps();
            $table->softDeletes(); // adds deleted_at column
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tp_truck_agency_rates');
    }
};
