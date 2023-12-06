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
        Schema::create('white_lists', function (Blueprint $table) {
            $table->id();
            $table->string('ip');
            $table->string('env');
            $table->string('desc');
            $table->foreignId('who');

            $table->unique(['ip', 'env']);

            $table->timestamps();
        });

        $whitelistIps = [
            "10.10.10.2",
            "10.10.10.3",
            "10.10.10.4",
            "10.10.10.5",
            "10.10.10.6",
            "10.10.10.7",
            "10.10.10.8",
            "10.10.10.9",
            "10.10.10.10",
            "10.10.10.11",
            "10.10.10.12",
            "10.10.10.13",
            "10.10.10.14",
            "10.10.10.16",
            "10.10.10.17",
//            "31.223.75.12",//fron
//            "195.175.202.174"//front
        ];

//        foreach ($whitelistIps as $whitelistIp) {
//            \App\Models\WhiteList::upsert([
//                'ip' => $whitelistIp,
//                'desc' => now()->format('d.m.Y H:i:s'),
//                'who' => 77,
//                'env' => config('app.env'),
//            ], ['ip', 'env']);
//        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('white_lists');
    }
};
