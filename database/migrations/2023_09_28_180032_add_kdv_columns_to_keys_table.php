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
        Schema::table('keys', function (Blueprint $table) {
            Schema::table('keys', function (Blueprint $table) {
                $table->unsignedDouble('percent_kdv')->default(0);
            });
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('keys', function (Blueprint $table) {
            $table->dropColumn('percent_kdv');
        });
    }
};
