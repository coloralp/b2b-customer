<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('games', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->char('uuid', 36)->index('uuid');
            $table->string('name')->index('name');
            $table->integer('stock');
            $table->integer('category_id')->index('category_id');
            $table->integer('publisher_id')->index('publisher_id');
            $table->integer('language_id')->index('language_id')->default(3);
            $table->unsignedInteger('status')->index('status');
            $table->integer('min_sales');
            $table->decimal('avg_cost', 10)->nullable();
            $table->string('image_name')->nullable();
            $table->integer('region_id')->index('region_id');
            $table->unsignedInteger('category_type')->index('category_type');
            $table->string('description');
            $table->timestamps();
            $table->double('amount', 15, 4)->default(0);

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('games');
    }
};
