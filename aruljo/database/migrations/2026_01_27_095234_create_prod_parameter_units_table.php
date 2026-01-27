<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('prod_parameter_units', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('prod_parameter_id');
            $table->string('unit', 20);
            $table->string('description')->nullable();
            $table->unsignedBigInteger('modified_by')->nullable();
            $table->timestamps();

            // Reference the correctly prefixed table
            $table->foreign('prod_parameter_id')
                  ->references('id')->on('prod_parameters')
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prod_parameter_units');
    }
};
