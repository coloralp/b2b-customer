<?php

use App\Enums\KeyKdvStatus;
use App\Enums\KeyStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('keys', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->char('uuid', 36)->index('uuid');
            $table->integer('who')->index('who');
            $table->integer('moneybox_id')->index('moneybox_id')->nullable();
            $table->integer('supplier_id')->index('supplier_id');
            $table->integer('game_id')->index('game_id');
            $table->string('key')->index('key')->unique();
            $table->string('description')->nullable()->comment('invoice no yerine bir şey girilimiyormuş yanı bir fatura numarası girilmiyormuş açıklma olarak kullanılıyormuş');
            $table->unsignedInteger('status')->index('status');
            $table->unsignedInteger('is_kdv')->index('is_kdv')->default(KeyKdvStatus::KDV_YOK->value);
            $table->double('kdv_amount', 15, 4)->default(0);


            //oluştururken doldurulması zorunlu üstekilerde
            $table->double('cost', 15, 4);
            $table->integer('cost_currency_id')->index('cost_currency_id');
            $table->double('cost_convert_euro', 15, 4);

            $table->foreignId('sale_info_id')->index()->nullable();

            $table->integer('order_id')->index('order_id')->nullable();
            $table->timestamp('sell_date')->nullable();

            $table->timestamps();

//            $table->string('invoice_no');
//            $table->integer('where_was_it_sold')->index('where_was_it_sold');
//            $table->enum('transaction_type', ['1', '2'])->index('transaction_type');
//            $table->integer('transaction_id')->index('transaction_id');
            //$table->enum('type', ['1', '2'])->index('type');
//            $table->enum('cost_type', ['1', '2', '3', '4'])->index('cost_type');
//            $table->enum('amount_type', ['0', '1', '2', '3', '4'])->index('amount_type');
//            $table->integer('order_by')->index('order_by')->default(null);
//            $table->integer('reservation_id')->index('reservation_id');
//            $table->integer('reservation_by')->index('reservation_by');
            $table->index(['key'], 'key_index');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('keys');
    }
};
