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
        Schema::create('analytics', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('analyzable_id');
            $table->string('analyzable_type');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('source')->nullable();
            $table->tinyInteger('level')->nullable();
            $table->bigInteger('started_at');
            $table->bigInteger('completed_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('analytics');
    }
};
