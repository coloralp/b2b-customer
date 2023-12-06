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
        Schema::table('jar_transaction_requests', function (Blueprint $table) {
            $table->unsignedInteger('status')->default(\App\Enums\JarTransactionRequestEnum::CREATED->value)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('jar_transaction_requests', function (Blueprint $table) {
            $table->boolean('is_approve')->default(false)->change();

        });
    }
};
