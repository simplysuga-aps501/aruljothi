<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tp_truck_capacities', function (Blueprint $table) {
            $table->id();

            $table->foreignId('product_id')
                  ->constrained('products')
                  ->cascadeOnDelete();

            $table->foreignId('truck_type_id')
                  ->constrained('tp_truck_types')
                  ->cascadeOnDelete();

            // New column for body type
            $table->enum('body_type', ['truck', 'open_body_truck']);

            $table->integer('max_units')->default(0);

            $table->timestamps();

            // Ensure one row per product + truck type + body type
            $table->unique(['product_id', 'truck_type_id', 'body_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tp_truck_capacities');
    }
};
