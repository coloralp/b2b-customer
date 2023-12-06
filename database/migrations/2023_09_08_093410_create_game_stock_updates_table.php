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
        Schema::create('game_stock_updates', function (Blueprint $table) {
            $table->id();
            $table->datetime('date');
            $table->foreignId('game_id')->index();
            $table->foreignId('supplier_id')->index();
            $table->integer('old_stock');
            $table->integer('new_stock');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('game_stock_updates');
    }
};
