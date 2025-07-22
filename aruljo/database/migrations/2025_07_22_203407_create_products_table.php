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
            $table->string('name'); // Product name
            $table->text('description')->nullable(); // Optional product description

            // Replace this:
            // $table->string('category')->nullable();

            // With this:
            $table->foreignId('product_template_id')->nullable()->constrained('product_templates')->nullOnDelete();

            $table->foreignId('unit_id')->nullable()->constrained('units')->nullOnDelete(); // Foreign key to units
            $table->foreignId('hsncode_id')->nullable()->constrained('hsncodes')->nullOnDelete(); // Foreign key to hsncodes
            $table->integer('stock_count')->default(0); // Total stock quantity
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
