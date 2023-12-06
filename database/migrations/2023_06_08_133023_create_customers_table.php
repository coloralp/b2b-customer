<?php

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
        Schema::create('customers', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->char('uuid', 36)->index('uuid');
            $table->double('currency_balance_remaining_balance', 15, 4);
            $table->double('currency_balance_spent_balance', 15, 4);
            $table->double('currency_balance', 15, 4);
            $table->unsignedInteger('currency');
            $table->string('location');
            $table->string('name')->index('name');
            $table->string('address');
            $table->string('vat_number');
            $table->string('company_registration_number');
            $table->string('related_person');
            $table->string('email');
            $table->string('web_site');
            $table->string('payment_method');
            $table->double('balance', 15, 4);
            $table->double('spent_balance', 15, 4);
            $table->double('remaining_balance', 15, 4);
            $table->string('info');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('customers');
    }
};
