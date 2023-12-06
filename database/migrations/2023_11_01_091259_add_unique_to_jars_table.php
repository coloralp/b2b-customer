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
        Schema::table('jars', function (Blueprint $table) {
            $table->unique('owner_id');
            $table->dropIndex('jars_owner_id_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('jars', function (Blueprint $table) {
            $table->dropIndex('jars_owner_id_index');
        });
    }
};
