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
         Schema::create('distance_cache', function (Blueprint $table) {
                    $table->id();
                    $table->unsignedBigInteger('from_location_id');
                    $table->unsignedBigInteger('to_location_id');
                    $table->decimal('distance_km', 8, 2);
                    $table->decimal('duration_minutes', 8, 2);
                    $table->timestamp('last_updated')->nullable();
                    $table->timestamps();

                    $table->unique(['from_location_id', 'to_location_id']);
                    $table->foreign('from_location_id')->references('id')->on('distance_pincodes');
                    $table->foreign('to_location_id')->references('id')->on('distance_pincodes');
                });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('distance_caches');
    }
};
