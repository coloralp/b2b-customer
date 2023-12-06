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
        Schema::table('api_sale_limits', function (Blueprint $table) {
            $table->foreignId('game_id')->nullable()->constrained('games')->cascadeOnDelete()->cascadeOnUpdate();
            $table->dropIndex('api_sale_limits_marketplace_id_unique');
            $table->unique(['marketplace_id', 'game_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('api_sale_limits', function (Blueprint $table) {
            $table->dropForeign('api_sale_limits_game_id_foreign');
            $table->dropIndex('api_sale_limits_game_id_foreign');
            $table->dropColumn('game_id');
        });
    }
};
