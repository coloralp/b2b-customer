<?php

use App\Enums\KeyStatus;
use App\Enums\OrderStatus;
use App\Enums\OrderTypeEnum;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('order_type')->default(OrderTypeEnum::FROM_API->value)
                ->comment('1 => APi siparişi 2 =>Customer siparişi');
            $table->string('order_code')->unique();
            $table->foreignId('who')->comment('Müşteri siaprişini kim oluşturdu')->nullable()->index();
            $table->decimal('total_amount', 15, 8)->comment('orderın total satış fiyatı');
            $table->foreignId('amount_currency_id')->index();
            $table->decimal('total_cost')->comment('ordera verilen keylerin toplam cost değeri')->nullable();
            $table->foreignId('cost_currency_id')->nullable()->index();
            $table->unsignedInteger('status')->comment('Enums/OrderStatus de bilrtildi');
            $table->dateTime('reservation_time')->nullable();
            $table->softDeletes();

            $table->text('text');

            //from api
            $table->text('reservation_id')->comment('eğer aği siparişi ise oluşturulan rezervasyon id')->nullable();
            $table->foreignId('match_id')->comment('eğer aği siparişi ise db deki')->nullable()->index();

            //to customer
            $table->foreignId('customer_id')->nullable()->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
