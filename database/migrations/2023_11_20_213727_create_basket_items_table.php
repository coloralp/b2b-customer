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
        Schema::create('basket_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('who')->constrained('users')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId('game_id')->constrained('games')->cascadeOnUpdate()->cascadeOnDelete();
            $table->unsignedInteger('qty');
            $table->decimal('unit_price', 10);

            $table->unique(['who', 'game_id']);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('basket_items');
    }
};
