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
        Schema::create('jar_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('jar_id')->index();
            $table->foreignId('processed_by')->index();
            $table->decimal('amount', 10, 2);
            $table->unsignedInteger('amount_currency');
            $table->unsignedInteger('jar_transaction_type')->index();
            $table->foreignId('currency_info_id');
            $table->decimal('amount_convert_eur', 10);
            $table->decimal('amount_convert_jar', 10);

            $table->decimal('old_balance', 10);
            $table->decimal('new_balance', 10);


            $table->foreignId('delete_by')->index()->nullable();
            $table->text('description')->nullable();
            $table->foreignId('order_id')->index()->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jar_transactions');
    }
};
