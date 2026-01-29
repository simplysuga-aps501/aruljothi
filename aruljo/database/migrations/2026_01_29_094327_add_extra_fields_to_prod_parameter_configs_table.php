<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('prod_parameter_configs', function (Blueprint $table) {
            // Display order for UI rendering
            $table->unsignedInteger('sort_order')->default(0)->after('prod_parameter_id');

            // Marks whether parameter is required
            $table->boolean('is_required')->default(false)->after('sort_order');

            // Optional default value for the parameter
            $table->text('default_value')->nullable()->after('is_required');

            // Input rendering type (e.g., numeric, option, text, boolean, etc.)
            $table->string('input_type', 50)->nullable()->after('default_value');

            // Logical grouping of parameters (e.g., Dimensions, Material)
            $table->string('group_name')->nullable()->after('input_type');

            // Active flag to enable/disable the parameter
            $table->boolean('is_active')->default(true);
        });
    }

    public function down(): void
    {
        Schema::table('prod_parameter_configs', function (Blueprint $table) {
            $table->dropColumn([
                'sort_order',
                'is_required',
                'default_value',
                'input_type',
                'group_name',
                'is_active', // drop is_active as well
            ]);
        });
    }
};
