<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        Schema::dropIfExists('publishers');
    }

    public function down(): void
    {
        Schema::create('publishers', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->char('uuid', 36)->index('uuid');
            $table->string('name')->fulltext('IndexName');
            $table->timestamps();

            $table->index(['name'], 'name');
        });
    }
};
