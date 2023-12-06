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
        Schema::create('frequency_sales_summaries', function (Blueprint $table) {
            $table->id();
            $table->json('yesterday')->nullable(true);
            $table->json('today')->nullable(true);
            $table->json('this_week')->nullable(true);
            $table->json('this_month')->nullable(true);
            $table->json('this_year')->nullable(true);
            $table->json('two_week')->nullable(true);
            $table->json('last_month')->nullable(true);
            $table->json('general_sales')->nullable(true);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('frequency_sales_summaries');
    }
};
