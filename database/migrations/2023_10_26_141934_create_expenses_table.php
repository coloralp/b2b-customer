<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('who')->index();
            $table->decimal('amount', 10);
            $table->unsignedInteger('amount_currency')->index();
            $table->decimal('amount_convert_eur', 10);
            $table->foreignId('currency_info_id')->index();
            $table->text('description')->nullable(false);

            $table->softDeletes();
            $table->timestamps();
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};
