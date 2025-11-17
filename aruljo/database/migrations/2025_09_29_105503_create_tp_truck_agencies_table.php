<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tp_truck_agencies', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Agency name
            $table->string('contact_person')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->text('address')->nullable();
            $table->string('gst_number')->nullable();
            $table->decimal('default_per_km_rate', 10, 2)->nullable();
            $table->timestamps();
            $table->softDeletes(); // adds deleted_at column for soft delete
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tp_truck_agencies');
    }
};
