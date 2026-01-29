<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('prod_parameter_unit_configs', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('prod_template_id');
            $table->unsignedBigInteger('prod_parameter_id');
            $table->unsignedBigInteger('prod_parameter_unit_id')->nullable();
            $table->boolean('allow_custom_unit')->default(false);
            $table->unsignedBigInteger('modified_by')->nullable();

            $table->timestamps();

            // Foreign keys
            $table->foreign('prod_template_id')->references('id')->on('prod_templates')->onDelete('cascade');
            $table->foreign('prod_parameter_id')->references('id')->on('prod_parameters')->onDelete('cascade');
            $table->foreign('prod_parameter_unit_id')->references('id')->on('prod_parameter_units')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prod_parameter_unit_configs');
    }
};
