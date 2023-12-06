<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('api_sale_limits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('marketplace_id')->unique();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('hourly');
            $table->unsignedInteger('daily');
            $table->unsignedInteger('monthly');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('api_sale_limits');
    }
};
