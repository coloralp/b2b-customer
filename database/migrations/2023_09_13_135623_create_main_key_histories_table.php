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
        Schema::create('main_key_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('key_id')->index()->nullable();
            $table->foreignId('order_id')->index()->nullable();
            $table->unsignedInteger('status')->index();

            $table->foreignId('parent_id')->nullable()->index();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('main_key_histories');
    }
};
