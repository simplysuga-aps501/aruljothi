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
        Schema::create('units', function (Blueprint $table) {
                    $table->id(); // BIGINT Primary Key
                    $table->string('name'); // e.g., KG, NOS, etc.
                    $table->string('modified_by')->nullable(); // User name (can be nullable)
                    $table->timestamps(); // created_at, updated_at
                });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('units');
    }
};
