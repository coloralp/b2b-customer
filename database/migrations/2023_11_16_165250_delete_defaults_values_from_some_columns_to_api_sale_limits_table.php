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
            $table->unsignedInteger('hourly')->default(0)->change();
            $table->unsignedInteger('daily')->default(0)->change();
            $table->unsignedInteger('monthly')->default(0)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('api_sale_limits', function (Blueprint $table) {
            $table->unsignedInteger('hourly')->change();
            $table->unsignedInteger('daily')->change();
            $table->unsignedInteger('monthly')->change();
        });
    }
};
