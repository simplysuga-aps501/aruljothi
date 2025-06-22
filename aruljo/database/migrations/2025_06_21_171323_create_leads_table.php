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
        Schema::create('leads', function (Blueprint $table) {
            $table->id(); // Lead ID (auto-increment)
            $table->string('platform');
            $table->date('lead_date');
            $table->string('buyer_name');
            $table->string('buyer_location')->nullable();
            $table->string('buyer_contact');
            $table->string('platform_keyword')->nullable();
            $table->text('product_detail')->nullable();
            $table->string('delivery_location')->nullable();
            $table->date('expected_delivery_date')->nullable();
            $table->text('remarks')->nullable();
            $table->date('follow_up_date')->nullable();
            $table->string('status')->default('New Lead');
            $table->string('assigned_to')->nullable();
            $table->json('user_log')->nullable();
            $table->string('modified_by')->nullable();
            $table->softDeletes();
            $table->timestamps(); // added_on, updated_on
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leads');
    }
};
