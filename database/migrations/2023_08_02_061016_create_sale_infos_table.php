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
        Schema::create('sale_infos', function (Blueprint $table) {
            $table->id();

            //satarken doldurulması zorunlu
            $table->double('amount', 15, 4)->default(0);
            $table->integer('amount_currency_id')->index('amount_currency_id')->nullable();
            $table->double('amount_convert_euro', 15, 4)->nullable();
            $table->json('amount_currency')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sale_infos');
    }
};
