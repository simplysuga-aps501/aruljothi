<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('quote_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quotation_id')->constrained('quotations')->onDelete('cascade');
            $table->integer('version_number');
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('gst_rate', 5, 2)->default(18);
            $table->decimal('total_amount', 12, 2)->default(0);
            $table->decimal('net_total', 12, 2)->default(0);
            $table->decimal('cost_per_kg', 10, 3)->default(0);
            $table->decimal('distance_km', 8, 2)->default(0);
            $table->foreignId('delivery_location_id')->nullable()->constrained('distance_pincodes');
            $table->boolean('is_active')->default(false);
            $table->text('remarks')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->timestamps();
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quote_versions');
    }
};
