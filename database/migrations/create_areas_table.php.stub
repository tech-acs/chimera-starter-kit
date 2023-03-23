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
        Schema::create('areas', function (Blueprint $table) {
            $table->id();

            $table->string('code', 50)->index();
            $table->jsonb('name', 100);
            $table->tinyInteger('level')->index();
            $table->geometry('geom')->nullable();

            $table->timestamps();
        });

        DB::statement("ALTER TABLE areas ADD COLUMN path ltree NOT NULL");
        DB::statement("ALTER TABLE areas ADD CONSTRAINT areas_path_unique UNIQUE(path)");
        DB::statement("CREATE INDEX areas_path_gist_idx ON areas USING gist(path)");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('areas');
    }
};
