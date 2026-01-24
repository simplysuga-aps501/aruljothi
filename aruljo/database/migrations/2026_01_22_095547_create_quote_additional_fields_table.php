<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('quote_additional_fields', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quote_version_id')->constrained('quote_versions')->onDelete('cascade');
            $table->string('heading');
            $table->text('content')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

    }

    public function down(): void
    {
        Schema::dropIfExists('quote_additional_fields');
    }
};
