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
        Schema::create('prod_parameter_values', function (Blueprint $table) {
                    $table->id();
                    $table->unsignedBigInteger('product_id');
                    $table->unsignedBigInteger('prod_parameter_id');
                    $table->string('value');
                    $table->unsignedBigInteger('unit_id')->nullable();
                    $table->unsignedBigInteger('modified_by')->nullable();
                    $table->timestamps();

                    $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
                    $table->foreign('prod_parameter_id')->references('id')->on('prod_parameters')->onDelete('cascade');
                    $table->foreign('unit_id')->references('id')->on('units')->nullOnDelete();
                    $table->foreign('modified_by')->references('id')->on('users')->nullOnDelete();
                });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('prod_parameter_values');
    }
};
