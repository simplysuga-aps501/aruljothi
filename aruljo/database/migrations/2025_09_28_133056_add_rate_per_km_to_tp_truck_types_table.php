<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tp_truck_types', function (Blueprint $table) {
            $table->decimal('rate_per_km', 8, 2)->after('capacity_kg')
                  ->default(0);
        });
    }

    public function down(): void
    {
        Schema::table('tp_truck_types', function (Blueprint $table) {
            $table->dropColumn('rate_per_km');
        });
    }
};
