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
        Schema::create('moneyboxes', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->char('uuid', 36)->index('uuid');
            $table->string('name')->index('name');
            $table->integer('supplier_id')->index('supplier_id');
            $table->double('balance', 15, 4);
            $table->double('spent_balance', 15, 4);
            $table->double('remaining_balance', 15, 4);
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
        Schema::dropIfExists('moneyboxes');
    }
};
