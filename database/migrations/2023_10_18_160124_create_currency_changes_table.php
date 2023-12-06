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
        Schema::create('currency_changes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('change_by')->index();
            $table->foreignId('old_currency')->index();
            $table->foreignId('new_currency')->index();

            $table->decimal('old_balance', 10, 2);
            $table->decimal('new_balance', 10, 2);
            $table->decimal('amount', 10, 2);
            $table->foreignId('currency_info_id');
            $table->foreignId('jar_id')->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('currency_changes');
    }
};
