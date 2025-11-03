<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tp_min_km_multipliers', function (Blueprint $table) {
            $table->id();
            $table->integer('min_km');
            $table->integer('max_km')->nullable();
            $table->decimal('multiplier', 5, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tp_min_km_multipliers');
    }
};
