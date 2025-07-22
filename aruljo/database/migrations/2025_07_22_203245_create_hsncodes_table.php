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
       Schema::create('hsncodes', function (Blueprint $table) {
                   $table->id(); // BIGINT Primary Key
                   $table->string('name'); // HSN code (e.g., 6810, 7306 etc.)
                   $table->text('description')->nullable(); // Optional description
                   $table->string('modified_by')->nullable(); // Username or editor name
                   $table->timestamps(); // created_at, updated_at
               });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('h_s_n_codes');
    }
};
