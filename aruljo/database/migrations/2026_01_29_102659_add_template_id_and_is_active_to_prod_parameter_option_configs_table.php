<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('prod_parameter_option_configs', function (Blueprint $table) {
            // Add template ID for template-specific restrictions
            $table->foreignId('prod_template_id')
                  ->nullable()
                  ->constrained('prod_templates')
                  ->after('prod_parameter_id')
                  ->onDelete('cascade');

            // Add is_active flag to enable/disable options
            $table->boolean('is_active')
                  ->default(true)
                  ->after('abbreviation');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('prod_parameter_option_configs', function (Blueprint $table) {
            $table->dropForeign(['prod_template_id']);
            $table->dropColumn('prod_template_id');
            $table->dropColumn('is_active');
        });
    }
};
