<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tp_truck_types', function (Blueprint $table) {
            $table->decimal('unloading_charges_below_150', 12, 2)->default(0)->after('capacity_kg');
            $table->decimal('unloading_charges_above_150', 12, 2)->default(0)->after('unloading_charges_below_150');
        });
    }

    public function down(): void
    {
        Schema::table('tp_truck_types', function (Blueprint $table) {
            $table->dropColumn('unloading_charges_below_150');
            $table->dropColumn('unloading_charges_above_150');
        });
    }
};
