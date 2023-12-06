<?php

use App\Models\Summary;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('summaries', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('month_name');
            $table->string('month_id');
            $table->string('year_id');
            $table->dateTime('date')->default(now());
            $table->double('total_margin')->comment(' kar')->default(0.0);
            $table->double('total_giro')->comment(' ciro')->default(0.0);
            $table->double('total_cost')->comment(' maliyet veya gider')->default(0.0);
            $table->double('add_cost')->comment('ek gider')->default(0.0);
            $table->double('net_margin')->comment('net kazanç')->default(0.0);
            $table->json('by_category_info')->nullable(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('summaries');
    }
};
