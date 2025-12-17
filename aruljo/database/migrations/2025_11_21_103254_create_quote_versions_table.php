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

            // 💾 Financials
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('gst_rate', 5, 2)->default(18);
            $table->decimal('net_total', 12, 2)->default(0);
            $table->decimal('distance_km', 8, 2)->default(0);

            // 💬 Remarks & Meta
            $table->foreignId('delivery_location_id')->nullable()->constrained('distance_pincodes');
            $table->boolean('is_active')->default(false);
            $table->text('remarks')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users');

            // 🧾 Customer snapshot fields
            // 🧾 Customer snapshot fields
            $table->foreignId('customer_id')->nullable()->constrained('customers')->nullOnDelete();
            $table->string('customer_name', 150)->nullable();
            $table->string('customer_contact', 20)->nullable();
            $table->string('customer_address_line1', 255)->nullable();
            $table->string('customer_address_line2', 255)->nullable();
            $table->string('customer_district', 100)->nullable();
            $table->string('customer_state', 100)->nullable();
            $table->string('customer_pincode', 10)->nullable();
            $table->string('customer_gst_number', 25)->nullable();


            // 🖨️ PDF content fields
            $table->date('pdf_date')->nullable();
            $table->string('pdf_subject', 255)->nullable();
            $table->text('pdf_terms')->nullable();
            $table->text('pdf_delivery')->nullable();

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
