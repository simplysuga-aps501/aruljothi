<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        //Transport Truck Types
        Schema::create('tp_truck_types', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique(); // e.g. "3 Ton", "6 Ton"
            $table->integer('capacity_kg');   // Max load capacity in kg
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tp_truck_types');
    }
};
