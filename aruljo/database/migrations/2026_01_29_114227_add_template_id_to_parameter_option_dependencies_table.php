<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('prod_parameter_option_dependencies', function (Blueprint $table) {
            // Just add a nullable template_id column, no FK, no index
            $table->unsignedBigInteger('prod_template_id')->nullable()->after('id');
            $table->boolean('is_required')->default(true)->after('req_param_id');
        });
    }

    public function down(): void
    {
        Schema::table('prod_parameter_option_dependencies', function (Blueprint $table) {
            // Drop the column
            $table->dropColumn('prod_template_id');
        });
    }
};
