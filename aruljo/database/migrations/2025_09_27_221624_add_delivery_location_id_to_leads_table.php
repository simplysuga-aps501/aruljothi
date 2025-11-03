<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            // Add delivery_location_id column
            $table->unsignedBigInteger('delivery_location_id')
                  ->nullable()
                  ->after('product_detail');;

            // Add foreign key constraint
            $table->foreign('delivery_location_id')
                  ->references('id')
                  ->on('distance_pincodes')
                  ->onUpdate('cascade')
                  ->onDelete('set null');

            // 🔥 Drop old text column now that it's migrated
            if (Schema::hasColumn('leads', 'delivery_location')) {
                $table->dropColumn('delivery_location');
            }
        });
    }

    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            // Remove FK and column
            $table->dropForeign(['delivery_location_id']);
            $table->dropColumn('delivery_location_id');

            // Re-add the old column (so rollback works)
            $table->string('delivery_location')->nullable();
        });
    }
};
