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
        Schema::create('jar_transaction_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('jar_id')->index();
            $table->foreignId('processed_by')->index();
            $table->decimal('amount', 10);
            $table->unsignedInteger('amount_currency');
            $table->unsignedInteger('jar_transaction_type');
            $table->foreignId('currency_info_id');
            $table->decimal('amount_convert_eur', 10);
            $table->decimal('will_add_amount', 10);
            $table->boolean('is_approve')->default(false);

            $table->foreignId('updated_by')->index()->nullable();

            $table->text('description')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jar_transaction_requests');
    }
};
