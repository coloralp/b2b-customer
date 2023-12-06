<?php

use App\Enums\KeyStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::dropIfExists('key_status_histories');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('key_status_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('key_id')->index()->nullable();
            $table->foreignId('order_id')->index()->nullable();
            $table->unsignedInteger('status')->index();
            $table->softDeletes();
            $table->timestamps();
        });
    }
};
