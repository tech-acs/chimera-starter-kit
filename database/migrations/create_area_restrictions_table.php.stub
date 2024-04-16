<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
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
        Schema::create('area_restrictions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained();
            $table->tinyInteger('level')->index();

            $table->timestamps();
        });

        DB::statement("ALTER TABLE area_restrictions ADD COLUMN path ltree");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('area_restrictions');
    }
};
