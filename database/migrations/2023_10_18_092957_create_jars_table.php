<?php

use App\Enums\CurrencyEnum;
use App\Models\Jar;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {

        if (!Schema::hasTable('jars')) {
            Schema::create('jars', function (Blueprint $table) {
                $table->id();
                $table->foreignId('owner_id')->index();
                $table->string('name');
                $table->unsignedInteger('currency');
                $table->decimal('balance', 10, 2);
                $table->timestamps();
            });
        }

        $owner = User::whereEmail(Jar::OUR_JAR)->first();

        if ($owner and Jar::whereOwnerId($owner->id)->doesntExist()) {
            Jar::upsert([
                'owner_id' => $owner->id,
                'name' => $owner->name,
                'currency' => CurrencyEnum::EUR->value,
                'balance' => 0
            ], ['owner_id']);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jars');
    }
};
