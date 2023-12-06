<?php

use App\Enums\CategoryTypeEnum;
use App\Enums\MarketplaceName;
use App\Models\MarketplaceCommission;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('marketplace_commissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('marketplace_id')->index();
            $table->foreignId('category_id')->index();
            $table->unsignedDecimal('min');
            $table->unsignedDecimal('max');
            $table->unsignedDecimal('percent_value');
            $table->unsignedDecimal('const_value');

            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('marketplace_comissions');
    }
};
