<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tp_district_rates', function (Blueprint $table) {
            $table->id();

            // Link to distance_pincodes (acts as location)
            $table->unsignedBigInteger('location_id')->index();

            // Optional link to tp_offices
            $table->unsignedBigInteger('office_id')->nullable()->index();

            // Rate value
            $table->decimal('rate', 10, 2);

            // Optional remarks
            $table->text('remarks')->nullable();

            // Foreign key constraints
            $table->foreign('location_id')
                ->references('id')
                ->on('distance_pincodes')
                ->onDelete('cascade');

            $table->foreign('office_id')
                ->references('id')
                ->on('tp_offices')
                ->nullOnDelete();

            // Only one rate allowed per location
            $table->unique('location_id');

            // Soft deletes + created_by/updated_by tracking
            $table->softDeletes();

            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();

            $table->foreign('created_by')
                ->references('id')
                ->on('users')
                ->nullOnDelete();

            $table->foreign('updated_by')
                ->references('id')
                ->on('users')
                ->nullOnDelete();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tp_district_rates');
    }
};
