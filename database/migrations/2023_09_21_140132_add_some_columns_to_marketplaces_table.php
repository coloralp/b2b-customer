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
        Schema::table('market_places', function (Blueprint $table) {
            $table->foreignId('supplier_id')->nullable()->index();
            $table->foreignId('customer_id')->nullable()->index();
            $table->unsignedDecimal('commission_percent')->default(0);
            $table->unsignedDecimal('commission_const')->default(0);
        });
        foreach (\App\Models\MarketPlace::all() as $marketplace) {
            $marketplace->update([
                'supplier_id' => \App\Enums\MarketplaceName::supplier($marketplace->id),
                'customer_id' => \App\Enums\MarketplaceName::customer($marketplace->id),
                'commission_percent' => \App\Enums\MarketplaceName::commissionPercent($marketplace->id),
                'commission_const' => \App\Enums\MarketplaceName::commissionConst($marketplace->id),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('market_places', function (Blueprint $table) {
            $table->dropColumn('supplier_id');
            $table->dropColumn('customer_id');
            $table->dropColumn('commission_percent');
            $table->dropColumn('commission_const');
        });
    }
};
