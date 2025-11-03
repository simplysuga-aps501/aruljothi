<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('distance_pincodes', function (Blueprint $table) {
            $table->id();
            $table->string('pincode', 6);
            $table->float('latitude', 10, 6)->nullable();
            $table->float('longitude', 10, 6)->nullable();
            $table->string('place')->nullable();
            $table->string('district')->nullable();
            $table->string('state')->nullable();
            $table->timestamps();

            $table->index('pincode');
            $table->index(['latitude', 'longitude']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('distance_pincodes');
    }
};
