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
        Schema::create('auto_marketplaces', function (Blueprint $table) {
            $table->id();
            $table->foreignId('game_id')->index();
            $table->foreignId('marketplace_id')->index();
            $table->boolean('is_auto')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('auto_marketplaces');
    }
};
