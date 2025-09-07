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
        Schema::create('prod_parameters', function (Blueprint $table) {
                    $table->id();
                    $table->string('name');
                    $table->string('description')->nullable();
                    $table->string('abbreviation', 20)->nullable();
                    $table->string('unit', 20)->nullable();
                    $table->enum('input_type', ['select', 'number'])->default('select');
                    $table->unsignedBigInteger('modified_by')->nullable();
                    $table->timestamps();
                });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('prod_parameters');
    }
};
