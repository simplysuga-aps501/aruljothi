<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quote_truck_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quote_truck_id')->constrained('quote_trucks')->onDelete('cascade');
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->decimal('allocated_qty', 10, 2)->default(0);
            $table->decimal('weight_per_unit', 10, 2)->default(0);   // 🆕 product weight snapshot
            $table->decimal('max_allowed_qty', 10, 2)->default(0);   // 🆕 max allowed snapshot
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quote_truck_products');
    }
};
