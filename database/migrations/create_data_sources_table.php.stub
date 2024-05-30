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
        Schema::create('data_sources', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->jsonb('title')->nullable();
            $table->timestamp('start_date')->nullable();
            $table->timestamp('end_date')->nullable();
            $table->string('case_stats_component')->default('case-stats');
            $table->boolean('show_on_home_page')->default(false);
            $table->unsignedTinyInteger('rank')->nullable();

            $table->string('host');
            $table->string('port', 10);
            $table->string('database');
            $table->string('username');
            $table->text('password');
            $table->boolean('connection_active')->default(true);
            $table->string('driver', '25')->default('mysql');
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
        Schema::dropIfExists('data_sources');
    }
};
