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
        Schema::create('pageables', function (Blueprint $table) {
            $table->id();
            $table->foreignId('page_id');
            $table->foreignId('pageable_id');
            $table->string('pageable_type');
            $table->unsignedSmallInteger('rank')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('pageables');
    }
};
