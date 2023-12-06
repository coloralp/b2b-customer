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
        Schema::create('marketplace_match_games', function (Blueprint $table) {
            $table->id();
            $table->foreignId('game_id')->index();
            $table->text('product_id');
            $table->foreignId('marketplace_id')->index();
            $table->foreignId('who')->index();
            $table->boolean('status')->default(1);
            $table->text('offer_id');
            $table->double('amount_us');
            $table->string('amount_currency');
            $table->double('amount_api');

            $table->timestamps();

            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('marketplace_match_games');
    }
};
