<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tp_district_rates', function (Blueprint $table) {
            // Add truck_type_id column
            $table->unsignedBigInteger('truck_type_id')->after('location_id')->index();

            // Add foreign key
            $table->foreign('truck_type_id')
                ->references('id')
                ->on('tp_truck_types')
                ->onDelete('cascade');

            // Drop old unique constraint and add new one
            $table->dropUnique(['location_id']);
            $table->unique(['location_id', 'truck_type_id']);
        });
    }

    public function down(): void
    {
        Schema::table('tp_district_rates', function (Blueprint $table) {
            // Revert to original structure
            $table->dropForeign(['truck_type_id']);
            $table->dropColumn('truck_type_id');

            $table->dropUnique(['location_id', 'truck_type_id']);
            $table->unique('location_id');
        });
    }
};
