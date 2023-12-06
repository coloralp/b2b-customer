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
        Schema::dropIfExists('suppliers');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('suppliers', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->char('uuid', 36)->index('uuid');
            $table->string('name')->index('name');
            $table->string('address');
            $table->string('vat_number');
            $table->string('company_registration_number');
            $table->string('related_person');
            $table->string('email')->index('email');
            $table->string('web_site');
            $table->string('payment_method');
            $table->double('balance', 15, 4);
            $table->double('spent_balance', 15, 4);
            $table->double('remaining_balance', 15, 4);
            $table->string('info');
            $table->timestamps();
        });
    }
};
