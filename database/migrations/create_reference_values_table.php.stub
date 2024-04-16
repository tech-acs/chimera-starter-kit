<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

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
            $table->tinyInteger('level')->index();
            $table->string('indicator', 100)->index();
            $table->decimal('value', 12, 2);

            $table->timestamps();
        });

        DB::statement("ALTER TABLE reference_values ADD COLUMN path ltree");
        //DB::statement("ALTER TABLE reference_values ADD CONSTRAINT reference_values_path_unique UNIQUE(path)");
        DB::statement("CREATE INDEX reference_values_path_gist_idx ON reference_values USING gist(path)");
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
