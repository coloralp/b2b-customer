<?php

use App\Enums\KeyStatus;
use App\Models\KeyStatusTable;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('key_status', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('status_code');
            $table->string('status_name');
            $table->timestamps();
        });

        foreach (KeyStatus::cases() as $case) {
            if ($case->value !== -1) {
                KeyStatusTable::create([
                    'status_code' => $case->value,
                    'status_name' => $case->name
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('key_status');
    }
};
