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
        Schema::create('prod_parameter_option_dependencies', function (Blueprint $table) {
            $table->id();

            // FK → option from prod_parameter_option_configs
            $table->foreignId('option_id')
                ->constrained('prod_parameter_option_configs')
                ->onDelete('cascade');

            // FK → required parameter from prod_parameters
            $table->foreignId('req_param_id')
                ->constrained('prod_parameters')
                ->onDelete('cascade');

            $table->timestamps();

            $table->unique(['option_id', 'req_param_id'], 'product_option_required_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('prod_parameter_option_dependencies');
    }
};
