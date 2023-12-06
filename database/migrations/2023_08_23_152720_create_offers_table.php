<?php

use App\Enums\CurrencyEnum;
use App\Enums\OfferStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('offers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('created_by')->index();
            $table->foreignId('supplier_id')->index();
            $table->foreignId('game_id')->index();
            $table->integer('quantity')->default(0);
            $table->unsignedInteger('status')->default(OfferStatus::ACTIVE->value);
            $table->double('price');
            $table->unsignedInteger('currency_id')->index();
            $table->dateTime('offer_start_time')->nullable(true);
            $table->dateTime('offer_finish_time')->nullable(true);
            $table->text('description');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('offers');
    }
};
