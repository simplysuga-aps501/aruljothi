<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tp_offices', function (Blueprint $table) {
            $table->id();

            // Basic info
            $table->string('name');
            $table->string('contact_person')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->text('address')->nullable();

            // GST
            $table->string('gst_number')->nullable();

            // Relation to distance_pincodes (office base location)
            $table->unsignedBigInteger('location_id')->nullable();
            $table->foreign('location_id')
                ->references('id')
                ->on('distance_pincodes')
                ->onDelete('set null');

            // Preferred states & districts (JSON arrays)
            $table->json('preferred_states')->nullable();
            $table->json('preferred_districts')->nullable();

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
        Schema::dropIfExists('tp_offices');
    }
};
