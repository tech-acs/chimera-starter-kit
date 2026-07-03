<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('reference_values', function (Blueprint $table) {
            $table->foreign('indicator')
                ->references('indicator')
                ->on('reference_value_indicators')
                ->cascadeOnDelete();
        });
    }

    public function down()
    {
        Schema::table('reference_values', function (Blueprint $table) {
            $table->dropForeign(['indicator']);
        });
    }
};
