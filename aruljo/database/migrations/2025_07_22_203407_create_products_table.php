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
        Schema::create('products', function (Blueprint $table) {
            $table->id(); // BIGINT Primary Key
            $table->string('sku'); // Stock keeping unit
            $table->string('name')->unique(); // Product name
            $table->text('description')->nullable(); // Optional product description

            // Relation to product template
            $table->foreignId('prod_template_id')
                  ->nullable()
                  ->constrained('prod_templates')
                  ->nullOnDelete();

            // Relation to unit
            $table->foreignId('unit_id')
                  ->nullable()
                  ->constrained('units')
                  ->nullOnDelete();

            // Relation to HSN code
            $table->foreignId('hsncode_id')
                  ->nullable()
                  ->constrained('hsncodes')
                  ->nullOnDelete();

            $table->integer('stock_count')->default(0); // Total stock quantity

            // ✅ New fields
            $table->decimal('selling_price', 10, 2)->default(0);      // Selling price
            $table->decimal('manufacturing_cost', 10, 2)->default(0); // Manufacturing cost
            $table->decimal('weight_kg', 8, 2)->default(0);           // Weight per unit (kg)

            $table->unsignedBigInteger('modified_by')->nullable(); // User who last modified
            $table->timestamps(); // created_at, updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
