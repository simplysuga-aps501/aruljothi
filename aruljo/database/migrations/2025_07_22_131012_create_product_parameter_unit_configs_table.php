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
        Schema::create('product_parameter_unit_config', function (Blueprint $table) {
                   $table->id();
                   $table->foreignId('product_parameter_id')->constrained('product_parameters')->onDelete('cascade');
                   $table->foreignId('product_parameter_unit_id')->constrained('product_parameter_units')->onDelete('cascade');
                   $table->unsignedBigInteger('modified_by')->nullable();
                   $table->timestamps();
               });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_parameter_unit_configs');
    }
};
