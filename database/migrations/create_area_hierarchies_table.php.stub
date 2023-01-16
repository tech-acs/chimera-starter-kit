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
        Schema::create('area_hierarchies', function (Blueprint $table) {
            $table->id();
            $table->integer('index');
            $table->jsonb('name');

            $table->string('where_column')->nullable();
            $table->string('select_column')->nullable();

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
        Schema::dropIfExists('area_hierarchies');
    }
};
