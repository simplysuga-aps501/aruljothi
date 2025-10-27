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
            $table->string('name'); // Office name
            $table->string('contact_person')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->text('address')->nullable();

            // GST and default rates
            $table->string('gst_number')->nullable();
            $table->decimal('default_per_km_rate', 10, 2)->nullable();

            // 🔗 Relation to distance_pincodes (office base location)
            $table->unsignedBigInteger('location_id')->nullable();
            $table->foreign('location_id')
                  ->references('id')
                  ->on('distance_pincodes')
                  ->onDelete('set null');

            // 🌍 Preferred districts (stored as JSON or comma-separated list)
            $table->text('preferred_districts')->nullable();

            $table->timestamps();
            $table->softDeletes(); // adds deleted_at column for soft delete
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tp_offices');
    }
};
