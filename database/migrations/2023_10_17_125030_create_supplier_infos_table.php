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
        Schema::create('supplier_infos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supplier_id')->unique();
            $table->string('address');
            $table->string('vat_number');
            $table->string('company_registration_number');
            $table->foreignId('related_person')->index();
            $table->string('web_site');
            $table->string('payment_method');
            $table->double('balance', 15, 4)->default(0);
            $table->string('info');

            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('supplier_infos');
    }
};
