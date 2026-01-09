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
        Schema::create('inapplicables', function (Blueprint $table) {
            $table->id();
            $table->foreignId('area_hierarchy_id')->constrained();
            $table->foreignId('inapplicable_id');
            $table->string('inapplicable_type');
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
        Schema::dropIfExists('inapplicables');
    }
};
