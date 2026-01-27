<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('prod_parameter_configs', function (Blueprint $table) {
            // Drop old string column if it exists
            if (Schema::hasColumn('prod_parameter_configs', 'unit')) {
                $table->dropColumn('unit');
            }

            // Add the column first
            $table->unsignedBigInteger('unit_id')->nullable()->after('prod_parameter_id');
            $table->boolean('allow_custom_unit')->default(false)->after('unit_id');
        });

        // Add foreign key in a separate schema statement
        Schema::table('prod_parameter_configs', function (Blueprint $table) {
            $table->foreign('unit_id')
                  ->references('id')
                  ->on('prod_parameter_units')
                  ->onDelete('set null');
        });

    }

    public function down(): void
    {
        Schema::table('prod_parameter_configs', function (Blueprint $table) {
            $table->dropForeign(['unit_id']);
            $table->dropColumn(['unit_id', 'allow_custom_unit']);

            // Optionally restore old string column
            $table->string('unit', 20)->nullable()->after('prod_parameter_id');
        });
    }
};
