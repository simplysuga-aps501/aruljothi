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
        Schema::create('distance_cache', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('from_location_id');
            $table->unsignedBigInteger('to_location_id');
            $table->integer('distance_km')->nullable();
            $table->integer('duration_minutes')->nullable();
            $table->boolean('user_update')->default(false);
            $table->timestamp('last_updated')->nullable();
            $table->timestamps();

            $table->unique(['from_location_id', 'to_location_id']);
            $table->foreign('from_location_id')->references('id')->on('distance_pincodes')->onDelete('cascade');
            $table->foreign('to_location_id')->references('id')->on('distance_pincodes')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('distance_cache');
    }

};
