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
        Schema::create('real_matches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('game_id')->index();
            $table->string('product_api_id');
            $table->boolean('status')->default(true);
            $table->foreignId('marketplace_id')->index();

            $table->unique(['game_id', 'product_api_id']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('real_matches');
    }
};
