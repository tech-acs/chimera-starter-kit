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
        Schema::create('reference_values', function (Blueprint $table) {
            $table->id();

            $table->string('code', 50)->index();
            $table->tinyInteger('level')->index();
            $table->string('indicator', 100)->index();
            $table->decimal('value', 12, 2);

            $table->unique('code', 'level', 'indicator');

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
        Schema::dropIfExists('reference_values');
    }
};
